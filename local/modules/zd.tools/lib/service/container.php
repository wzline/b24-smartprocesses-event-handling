<?php
namespace ZD\Tools\Service;

if (!\Bitrix\Main\Loader::includeModule('crm')) {
    return;
}

class Container extends \Bitrix\Crm\Service\Container
{
    public function getFactory(int $entityTypeId): ?\Bitrix\Crm\Service\Factory
    {
        $customClass = '\ZD\Tools\Service\Factory\Dynamic_' . $entityTypeId;
        if (class_exists($customClass, true)) {
            $type = $this->getTypeByEntityTypeId($entityTypeId);
            return new $customClass($type);
        }
        
        return parent::getFactory($entityTypeId);
    }
}