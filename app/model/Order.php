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
     * @var \Nette\DateTime Čas odběru objednávky
     */
    private $pickup_at = null;

    /**
     * @var array Jednotlivé položky objednávky
     */
    private $order_items;

    /**
     * @var int Identifikátor akce
     */
    private $action_id;

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

    public function getActionId(){
        return $this->action_id;
    }

    public function setActionId($id){
        $this->action_id = $id;
    }

    public  function getPickUpDate(){
        return $this->pickup_at;
    }

    public function setPickUpDate(\Nette\DateTime $date){
        $this->pickup_at = $date;
    }

    public function getTotalItemsCount(){
        $sum = 0;
        foreach($this->order_items as $item){
            $sum += $item->count;
        }
        return $sum;
    }

    public function getTotalPrice(){
        $sum = 0;
        foreach($this->order_items as $item){
            $sum += $item->count * $item->price_vat_included;
        }
        return $sum;
    }
}