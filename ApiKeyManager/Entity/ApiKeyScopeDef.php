<?php

namespace Cav7\ApiKeyManager\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null $scope_id
 * @property string $scope_name
 * @property string $title
 * @property string $description
 * @property string $permission_group_id
 * @property string $permission_id
 * @property bool $is_active
 * @property int $display_order
 *
 * GETTERS
 * @property-read bool $is_gated
 */
class ApiKeyScopeDef extends Entity
{
    public function getIsGated(): bool
    {
        return $this->permission_group_id !== '' && $this->permission_id !== '';
    }

    protected function _preSave(): void
    {
        $group = trim($this->permission_group_id);
        $perm  = trim($this->permission_id);

        if ($group !== $this->permission_group_id)
        {
            $this->set('permission_group_id', $group);
        }
        if ($perm !== $this->permission_id)
        {
            $this->set('permission_id', $perm);
        }

        if (($group !== '' && $perm === '') || ($group === '' && $perm !== ''))
        {
            $this->error(\XF::phrase('cav7_api_scope_permission_pair_required'), 'permission_group_id');
        }

        if (!preg_match('/^[a-z][a-z0-9_:]*$/', $this->scope_name))
        {
            $this->error(\XF::phrase('cav7_api_scope_name_invalid'), 'scope_name');
        }
    }

    public static function getStructure(Structure $structure): Structure
    {
        $structure->table      = 'xf_cav7_api_key_scope_def';
        $structure->shortName  = 'Cav7\ApiKeyManager:ApiKeyScopeDef';
        $structure->primaryKey = 'scope_id';
        $structure->columns = [
            'scope_id'            => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'scope_name'          => ['type' => self::STR, 'required' => true, 'maxLength' => 50, 'unique' => true],
            'title'               => ['type' => self::STR, 'required' => true, 'maxLength' => 100],
            'description'         => ['type' => self::STR, 'default' => ''],
            'permission_group_id' => ['type' => self::STR, 'default' => '', 'maxLength' => 25],
            'permission_id'       => ['type' => self::STR, 'default' => '', 'maxLength' => 25],
            'is_active'           => ['type' => self::BOOL, 'default' => true],
            'display_order'       => ['type' => self::UINT, 'default' => 0],
        ];
        $structure->getters = [
            'is_gated' => true,
        ];

        return $structure;
    }
}
