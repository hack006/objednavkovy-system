<?php
namespace ResSys;

class OrderFieldModel extends AbstractModelDB{

    private $products;

    function __construct(\Nette\Database\Connection $db, \ResSys\ProductModel $products)
    {
        parent::__construct($db);
        $this->products = $products;
    }

    /**
     * Vytvoří novou položku objednávky
     *
     * @param int $product_id
     * @param int $order_id
     * @param int $count
     * @return bool|int|\Nette\Database\Table\IRow
     * @throws \Nette\InvalidArgumentException V případě, že neexistuje produkt s daným id
     */
    public function createOrderField($product_id, $order_id, $count){
        // získání produktu z db
        $product = $this->products->findAll()
            ->select('products.*, vats.value AS vat_value')
            ->get($product_id);

        if($product === false){
            throw new \Nette\InvalidArgumentException("Nelze vytvořit objednávku na produkt, který neexistuje!");
        }

        // uložení
        return $this->getTable()->insert(array(
            'name' => $product->name,
            'count' => $count,
            'price_without_tax' => $product->price_without_tax,
            'vat' => $product->vat_value,
            'order_id' => $order_id,
            'product_id' => $product_id
        ));
    }

    /**
     * Vrátí položky zadané objednávky
     *
     * @param int $order_id
     * @return \Nette\Database\Table\Selection
     */
    public function getOrderFields($order_id){
        return $this->getTable()
            ->select('order_fields.*, (price_without_tax * (1 + vat / 100)) AS price_tax_included,
                      (count * price_without_tax * (1 + vat / 100)) AS total_price_vat_included')
            ->where('order_id', $order_id);
    }
}