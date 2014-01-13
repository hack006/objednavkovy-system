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

    /**
     * Vrátí všechny produkty v objednávce
     *
     * @return array
     */
    public function getProducts(){
        return $this->order_items;
    }

    /**
     * Získá produkt z objednávky s daným id
     *
     * @param int $id Identifikátor produktu
     * @return mixed
     * @throws \Nette\InvalidArgumentException V případě, že produkt s daným id v objednávce není
     */
    public function getProduct($id){
        if(array_key_exists($id, $this->order_items)){
            return $this->order_items[$id];
        }
        throw new \Nette\InvalidArgumentException("Neexistující prvek!");
    }

    /**
     * Odebere produkt z objednávky
     *
     * @param int $id Id produktu
     */
    public function removeProduct($id){
        if(array_key_exists($id, $this->order_items)){
            unset($this->order_items[$id]);
        }
    }

    /**
     * Vrátí datum a čas vytvoření objednávky
     *
     * @return \Nette\DateTime
     */
    public function getCreationTime(){
        return $this->created_at;
    }

    /**
     * Vloží novou položku do objednávky
     *
     * @param int $id Id produktu
     * @param string $name Pojmenování
     * @param int $count Počet
     * @param int $price_wat_included Cena včetně daně
     */
    public function addNewItem($id, $name, $count, $price_wat_included){
        $this->order_items[$id] = new OrderItem($id, $name, $count, $price_wat_included);
    }

    /**
     * Vrátí id akce, pro kterou je objednávka vytvořena
     *
     * @return int
     */
    public function getActionId(){
        return $this->action_id;
    }

    /**
     * Nastaví id akce
     *
     * @param int $id Id akce
     */
    public function setActionId($id){
        $this->action_id = $id;
    }

    /**
     * Vrátí čas vyzvednutí / odběru objednávky
     *
     * @return \Nette\DateTime|null
     */
    public  function getPickUpDate(){
        return $this->pickup_at;
    }

    /**
     * Nastaví datum odběru objednávky
     *
     * @param \Nette\DateTime $date
     */
    public function setPickUpDate(\Nette\DateTime $date){
        $this->pickup_at = $date;
    }

    /**
     * Vrátí počet kusů produktů v objednávce.
     *
     * @return int Počet položek vobjednávce
     */
    public function getTotalItemsCount(){
        $sum = 0;
        foreach($this->order_items as $item){
            $sum += $item->count;
        }
        return $sum;
    }

    /**
     * Vrátí celkovou cenu produktů v objednávce
     *
     * @return int
     */
    public function getTotalPrice(){
        $sum = 0;
        foreach($this->order_items as $item){
            $sum += $item->count * $item->price_vat_included;
        }
        return $sum;
    }
}