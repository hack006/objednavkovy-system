<?php
namespace ResSys;

/**
 * Třída slouží k uchování rozpracované objednávky uživatele do doby než je finálně uložena do DB
 */
class Order{

    /**
     * @var \Nette\DateTime Čas vytvoření objednávky
     */
    private $created_at;

    /**
     * @var array Jednotlivé položky objednávky
     */
    private $order_items;


    public function __construct(){
        $this->created_at = new \Nette\DateTime();
        $this->order_items = array();
    }

    public function getProducts(){
        return $this->order_items;
    }

    public function getProduct($id){
        if(array_key_exists($id, $this->order_items)){
            return $this->order_items[$id];
        }
        throw new \Nette\InvalidArgumentException("Neexistující prvek!");
    }

    public function removeProduct($id){
        if(array_key_exists($id, $this->order_items)){
            unset($this->order_items[$id]);
        }
    }

    public function getCreationTime(){
        return $this->created_at;
    }

    public function addNewItem($id, $name, $count, $price_wat_included){
        $this->order_items[$id] = new OrderItem($id, $name, $count, $price_wat_included);
    }


}