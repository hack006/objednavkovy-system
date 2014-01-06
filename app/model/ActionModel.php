<?php

namespace ResSys;

class ActionModel extends AbstractModelDB{

    public function getAllTimeValid($timestamp){
        return $this->getTable()->where('UNIX_TIMESTAMP(visible_from) < ? AND UNIX_TIMESTAMP(visible_until) > ?',
            array($timestamp, $timestamp));
    }

    public function getActionProducts($action_id){
        return $this->connection->context->table('actions_products')
            ->where('action_id = ?', $action_id)->select('products.*, products.vats.value AS vat_value');
    }
}