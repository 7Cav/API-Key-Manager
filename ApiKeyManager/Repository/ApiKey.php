<?php

namespace Cav7\ApiKeyManager\Repository;

use Cav7\ApiKeyManager\Entity\ApiKey as ApiKeyEntity;
use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;

class ApiKeyRepository extends Repository
{
    public function generateKey(): array
    {
        $raw = 'cav7_' . \XF::generateRandomString(32);
        return [
            'raw'    => $raw,
            'hash'   => hash('sha256', $raw, true),
            'prefix' => substr($raw, 5, 8),
        ];
    }

    public function findKeysForAdminList(): Finder
    {
        return $this->finder('Cav7\ApiKeyManager:ApiKey')
            ->with('User')
            ->setDefaultOrder('created_date', 'DESC');
    }

    public function getKeyForUser(int $userId): ?ApiKeyEntity
    {
        return $this->finder('Cav7\ApiKeyManager:ApiKey')
            ->where('user_id', $userId)
            ->fetchOne();
    }

    public function createKeyForUser(int $userId): array
    {
        $keyData = $this->generateKey();

        /** @var ApiKeyEntity $key */
        $key = $this->em->create('Cav7\ApiKeyManager:ApiKey');
        $key->user_id      = $userId;
        $key->key_hash     = $keyData['hash'];
        $key->key_prefix   = $keyData['prefix'];
        $key->scope_read   = true;
        $key->is_active    = true;
        $key->created_date = \XF::$time;
        $key->save();

        return ['entity' => $key, 'raw' => $keyData['raw']];
    }

    public function rotateKeyForUser(ApiKeyEntity $key): string
    {
        $keyData = $this->generateKey();
        $key->key_hash   = $keyData['hash'];
        $key->key_prefix = $keyData['prefix'];
        $key->save();
        return $keyData['raw'];
    }
}
