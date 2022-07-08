<?php
namespace ZD\Tools\Service\Factory;

use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Context;
use Bitrix\Crm\Service\Operation;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable;

class Dynamic_174 extends \Bitrix\Crm\Service\Factory\Dynamic
{
    public function getUpdateOperation(Item $item, Context $context = null): Operation\Update
    {       
        $operation = parent::getUpdateOperation($item, $context);
        return $operation
            ->addAction(Operation::ACTION_BEFORE_SAVE, new Dynamic_174_BeforeUpdate())
            ->addAction(Operation::ACTION_AFTER_SAVE, new Dynamic_174_AfterUpdate())
        ;
    }
}

class Dynamic_174_BeforeUpdate extends Operation\Action 
{
    public function process(Item $item): Result
    {
        Dynamic_174_AfterUpdate::$needRunWorkflow = (bool) ($item->isChanged('ASSIGNED_BY_ID') && !$item->isNew());
        return new Result();
    }
}
class Dynamic_174_AfterUpdate extends Operation\Action 
{
    public static $needRunWorkflow = false;
    
    public function process(Item $item): Result
    {
        $result = new Result();
        if (self::$needRunWorkflow){
            $templateId = self::getWorkflowTemplateIdByName((int) $item->getEntityTypeId(), 'onAssignedChangedWorkflow');
            if ($templateId > 0) {
                $errors = [];
                $bpId = \CBPDocument::StartWorkflow($templateId, self::getDocumentId($item), [], $errors);
                $result->setData(['workflowId' => $bpId]);
            }
        }
        
        return $result;
    }
    
    private static function getWorkflowTemplateIdByName(int $ownerTypeId, string $name) : int
    {
        $result = 0;
        
        $params = [
            'filter' => [
                '=MODULE_ID' => 'crm',
                '=ENTITY' => \CCrmBizProcHelper::ResolveDocumentName($ownerTypeId),
                '=DOCUMENT_TYPE' => \CCrmOwnerType::ResolveName($ownerTypeId),
                '=NAME' => $name, //'OnTaskUpdateWorkflow'
            ],
            'select' => ['ID'],
            'order' => ['ID' => 'ASC'],
            'limit' => 1
            
        ];
        $r = WorkflowTemplateTable::getList($params);
        if ($t = $r->fetch()){
            $result = (int) $t['ID'];
        }
        
        return $result;
    }
    private static function getDocumentId(Item $item) : array
    {
        return [
            'crm',
            \CCrmBizProcHelper::ResolveDocumentName($item->getEntityTypeId()),
            \CCrmOwnerType::ResolveName($item->getEntityTypeId()) . '_' . $item->getId()
        ];
    }
}