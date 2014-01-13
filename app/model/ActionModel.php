<?php

namespace ResSys;

class ActionModel extends AbstractModelDB{

    /**
     * Vrátí z db všechny akce, které jsou platné v daný čas
     *
     * @param $timestamp Určení času, pro který validitu testujeme
     * @return \Nette\Database\Table\Selection
     */
    public function getAllTimeValid($timestamp){
        return $this->getTable()->where('UNIX_TIMESTAMP(visible_from) < ? AND UNIX_TIMESTAMP(visible_until) > ?',
            array($timestamp, $timestamp));
    }

    /**
     * Vrátí produkty pro zadanou akci
     *
     * @param int $action_id Id akce
     * @return \Nette\Database\Table\Selection
     */
    public function getActionProducts($action_id){
        return $this->connection->context->table('actions_products')
            ->where('action_id = ?', $action_id)->select('products.*, products.vats.value AS vat_value');
    }

}