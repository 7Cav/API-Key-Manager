<?php

namespace Cav7\ApiKeyManager\Admin\Controller;

use Cav7\ApiKeyManager\Entity\ApiKeyScopeDef;
use Cav7\ApiKeyManager\Repository\ApiKeyScopeDef as ApiKeyScopeDefRepo;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;

class ApiScope extends \XF\Admin\Controller\AbstractController
{
    protected function preDispatchController($action, ParameterBag $params): void
    {
        $this->assertAdminPermission('user');
    }

    public function actionIndex(): AbstractReply
    {
        $scopes = $this->getScopeRepo()->findScopesForList()->fetch();

        return $this->view(
            'Cav7\ApiKeyManager:ApiScope\List',
            'cav7_api_scope_list',
            ['scopes' => $scopes]
        );
    }

    public function actionAdd(): AbstractReply
    {
        /** @var ApiKeyScopeDef $scope */
        $scope = $this->em()->create('Cav7\ApiKeyManager:ApiKeyScopeDef');
        return $this->scopeAddEdit($scope);
    }

    public function actionEdit(ParameterBag $params): AbstractReply
    {
        $scope = $this->assertScopeExists($params->scope_id);
        return $this->scopeAddEdit($scope);
    }

    protected function scopeAddEdit(ApiKeyScopeDef $scope): AbstractReply
    {
        return $this->view(
            'Cav7\ApiKeyManager:ApiScope\Edit',
            'cav7_api_scope_edit',
            [
                'scope'            => $scope,
                'permissionGroups' => $this->getPermissionGroupChoices(),
                'permissionsByGroup' => $this->getPermissionsByGroup(),
            ]
        );
    }

    public function actionSave(ParameterBag $params): AbstractReply
    {
        $this->assertPostOnly();

        if ($params->scope_id)
        {
            $scope = $this->assertScopeExists($params->scope_id);
        }
        else
        {
            /** @var ApiKeyScopeDef $scope */
            $scope = $this->em()->create('Cav7\ApiKeyManager:ApiKeyScopeDef');
        }

        $this->scopeSaveProcess($scope)->run();

        return $this->redirect($this->buildLink('cav7-api-scopes'));
    }

    protected function scopeSaveProcess(ApiKeyScopeDef $scope): FormAction
    {
        $form = $this->formAction();

        $input = $this->filter([
            'scope_name'          => 'str',
            'title'               => 'str',
            'description'         => 'str',
            'permission_group_id' => 'str',
            'permission_id'       => 'str',
            'is_active'           => 'bool',
            'display_order'       => 'uint',
        ]);

        $form->basicEntitySave($scope, $input);

        return $form;
    }

    public function actionDelete(ParameterBag $params): AbstractReply
    {
        $scope = $this->assertScopeExists($params->scope_id);

        if ($this->isPost())
        {
            $scope->delete();
            return $this->redirect($this->buildLink('cav7-api-scopes'));
        }

        $viewParams = [
            'scope' => $scope,
        ];
        return $this->view(
            'Cav7\ApiKeyManager:ApiScope\Delete',
            'cav7_api_scope_delete',
            $viewParams
        );
    }

    protected function assertScopeExists(int $scopeId): ApiKeyScopeDef
    {
        /** @var ApiKeyScopeDef|null $scope */
        $scope = $this->em()->find('Cav7\ApiKeyManager:ApiKeyScopeDef', $scopeId);
        if (!$scope)
        {
            throw $this->exception($this->notFound(\XF::phrase('cav7_api_scope_not_found')));
        }
        return $scope;
    }

    protected function getPermissionGroupChoices(): array
    {
        $rows = $this->app()->db()->fetchAll(
            "SELECT DISTINCT permission_group_id
             FROM xf_permission
             ORDER BY permission_group_id"
        );
        $choices = ['' => '(none)'];
        foreach ($rows as $row)
        {
            $choices[$row['permission_group_id']] = $row['permission_group_id'];
        }
        return $choices;
    }

    protected function getPermissionsByGroup(): array
    {
        $rows = $this->app()->db()->fetchAll(
            "SELECT permission_group_id, permission_id
             FROM xf_permission
             ORDER BY permission_group_id, permission_id"
        );
        $byGroup = [];
        foreach ($rows as $row)
        {
            $byGroup[$row['permission_group_id']][] = $row['permission_id'];
        }
        return $byGroup;
    }

    protected function getScopeRepo(): ApiKeyScopeDefRepo
    {
        return $this->repository('Cav7\ApiKeyManager:ApiKeyScopeDef');
    }
}
