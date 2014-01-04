<?php
namespace ResSys;

class ProductModel extends AbstractModelDB {

    /**
     * @param $name Název
     * @param $description Popis
     * @param $price_without_tax Cena bez daně
     * @param $min_order_time_hours Minimální čas na vyřízení objednávky v hodinách. Lze kombinovat se dny.
     * @param $min_order_time_days Minimální čas na vyřízení objednávky v dnech. Lze kombinovat s hodinami.
     * @param $vat_id Identifikátor použité daně.
     * @return bool|int|\Nette\Database\Table\IRow
     */
    public function createProduct($name, $description, $price_without_tax, $min_order_time_hours, $min_order_time_days,
                                  $vat_id){

        return $this->getTable()->insert(array(
            'name' => $name,
            'description' => $description,
            'price_without_tax' => $price_without_tax,
            'min_order_time_hours' => $min_order_time_hours,
            'min_order_time_days' => $min_order_time_days,
            'vat_id' => $vat_id
        ));
    }
}