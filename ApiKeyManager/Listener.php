<?php

namespace Cav7\ApiKeyManager;

use XF\Mvc\Entity\Entity;

class Listener
{
    public static function entityPostSave(Entity $entity): void
    {
        if ($entity instanceof \XF\Entity\User)
        {
            self::onUserSave($entity);
            return;
        }
        if ($entity instanceof \XF\Entity\UserGroup)
        {
            self::onUserGroupSave($entity);
            return;
        }
        if ($entity instanceof \XF\Entity\PermissionEntry || $entity instanceof \XF\Entity\PermissionEntryContent)
        {
            self::onPermissionEntrySave($entity);
            return;
        }
        if ($entity instanceof \Cav7\ApiKeyManager\Entity\ApiKeyScopeDef)
        {
            self::onScopeDefSave($entity);
            return;
        }
    }

    protected static function onUserSave(\XF\Entity\User $user): void
    {
        $changes = $user->getChanges();
        if (!isset($changes['user_group_id']) && !isset($changes['secondary_group_ids']))
        {
            return;
        }

        $keyExists = (bool) \XF::db()->fetchOne(
            "SELECT key_id FROM xf_cav7_api_key WHERE user_id = ?",
            $user->user_id
        );
        if (!$keyExists)
        {
            return;
        }

        /** @var \Cav7\ApiKeyManager\Repository\ApiKey $repo */
        $repo = \XF::repository('Cav7\ApiKeyManager:ApiKey');
        $repo->recomputeScopesForUser((int) $user->user_id);
    }

    protected static function onUserGroupSave(\XF\Entity\UserGroup $group): void
    {
        self::enqueueJob(
            'cav7_recompute_scopes_group_' . $group->user_group_id,
            ['user_group_id' => (int) $group->user_group_id]
        );
    }

    protected static function onPermissionEntrySave(Entity $entry): void
    {
        if ($entry->isInsert() === false && empty($entry->getChanges()))
        {
            return;
        }

        if (isset($entry->user_id) && (int) $entry->user_id > 0)
        {
            self::enqueueJob(
                'cav7_recompute_scopes_user_' . $entry->user_id,
                ['user_id' => (int) $entry->user_id]
            );
            return;
        }
        if (isset($entry->user_group_id) && (int) $entry->user_group_id > 0)
        {
            self::enqueueJob(
                'cav7_recompute_scopes_group_' . $entry->user_group_id,
                ['user_group_id' => (int) $entry->user_group_id]
            );
        }
    }

    protected static function onScopeDefSave(\Cav7\ApiKeyManager\Entity\ApiKeyScopeDef $def): void
    {
        $changes = $def->getChanges();
        if (!$def->isInsert()
            && !isset($changes['permission_group_id'])
            && !isset($changes['permission_id'])
            && !isset($changes['is_active']))
        {
            return;
        }

        self::enqueueJob(
            'cav7_recompute_scopes_all',
            ['all' => true]
        );
    }

    protected static function enqueueJob(string $uniqueKey, array $data): void
    {
        \XF::app()->jobManager()->enqueueUnique(
            $uniqueKey,
            'Cav7\ApiKeyManager:RecomputeKeyScopes',
            $data
        );
    }
}
