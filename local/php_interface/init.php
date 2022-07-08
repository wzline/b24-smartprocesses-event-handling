<?php
if (\Bitrix\Main\Loader::includeModule('zd.tools')){
    Bitrix\Main\DI\ServiceLocator::getInstance()->addInstance('crm.service.container', new ZD\Tools\Service\Container());
}
