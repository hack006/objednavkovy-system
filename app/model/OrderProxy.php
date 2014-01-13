<?php
namespace ResSys;

class OrderProxy{

    /**
     * @var \Nette\Database\Connection Připojení k DB
     */
    private $connection;

    /**
     * @var Order Čistě datová struktura uchovávající data objednávky
     */
    private $order;

    /**
     * @var OrderModel Db přístup k entitě order.
     */
    private $orders;

    /**
     * @var OrderFieldModel Db přístup k entitě order field
     */
    private $order_fields;

    /**
     * Konstruktor
     *
     * @param \Nette\Database\Connection $db Připojení k DB
     * @param Order $order Objekt objednávky, který bude zapouzdřován. Pokud nepředán, vytvoří se nový.
     */
    public function __construct(\Nette\Database\Connection $db, \ResSys\Order &$order = NULL){
        $this->connection = $db;
        if(!isset($order)){
            $this->order = new Order();
        }
        else{
            $this->order = &$order;
        }
        $this->orders = new OrderModel($db);
        $this->order_fields = new OrderFieldModel($db, new ProductModel($db));
    }

    /**
     * Vrátí zapouzdřujicí objekt objednávky
     *
     * @return Order
     */
    public function getOrder(){
        return $this->order;
    }

    /**
     * Vrátí jednotlivé produkty objednávky
     *
     * @return array
     */
    public function getItems(){
        return $this->order->getProducts();
    }

    /**
     * Vrátí produkt zadaného identifikátoru
     *
     * @param $product_id Identifikátor
     * @return \ResSys\OrderItem
     * @throws \Nette\InvalidArgumentException V případě neexistujícího prvku.
     */
    public function getItem($product_id){
        return $this->getOrder()->getProduct($product_id);
    }

    /**
     * Přidá do objednávky produkt daného množství
     *
     * @param $product_id Id produktu
     * @param $count Počet přidávaného počtu
     * @throws \Nette\OutOfRangeException
     */
    public function addItem($product_id, $count){
        $count = intval($count);
        if(!is_int($count) || $count < 0){
            throw new \Nette\OutOfRangeException("Přidat lze pouze nezáporný celočíselný počet položek");
        }

        // Existuje již produkt v objednávce?
        try{
            $item = $this->getItem($product_id);
            $item->raiseCount($count);
        }
        catch(\Nette\InvalidArgumentException $e) {
            // Získat z db data k položce
            $product = $this->connection->context->table('products')
                ->select('products.*, vats.value AS tax_value')
                ->get($product_id); // @TODO zjistit zda vyhodi vyjimku pri neexistujicim id
            $product_price_wat_included = $product->price_without_tax * (1 + $product->tax_value / 100);
            $this->getOrder()->addNewItem($product_id, $product->name, $count, $product_price_wat_included);
        }
    }

    /**
     * Odstraní z objednávky daný počet produktů
     *
     * @param $product_id Id produktu
     * @param $count Počet kusů k odebrání
     * @throws \Nette\OutOfRangeException
     * @throws \Nette\InvalidArgumentException
     */
    public function removeItem($product_id, $count){
        $count = intval($count);
        if(!is_int($count) || $count < 0){
            throw new \Nette\OutOfRangeException("Odebrat lze pouze nezáporný celočíselný počet položek");
        }

        // Existuje již produkt v objednávce?
        try{
            $item = $this->getItem($product_id);
            $item->lowerCount($count);
        }
        catch(\Nette\InvalidArgumentException $e) {
            throw new \Nette\InvalidArgumentException("Produkt s daným id v objednávce neexistuje!");
        }
        catch(\Nette\OutOfRangeException $e){
            // již není počtem kusů validní
            $this->order->removeProduct($product_id);
            // je nutno zkontrolovat obsah objednávky, případně anulovat action_id pokud není k dispozici žádny item
            if($this->order->getTotalItemsCount() <= 0){
                $this->order->setActionId(null);
            }
        }

    }

    /**
     * Nastaví čas vyzvednutí
     *
     * @param \Nette\DateTime $date
     */
    public function setPickUpDate(\Nette\DateTime $date){
        // @todo validace
        $this->order->setPickUpDate($date);
    }

    /**
     * Vrátí čas vyzvednutí objednávky
     *
     * @return \Nette\DateTime|null
     */
    public function getPickUpDate(){
        return $this->order->getPickUpDate();
    }

    /**
     * Potvrdí objednávku a uloží ji do DB
     */
    public function confirmOrder($user_id){
        // uložit objednávku
        $order = $this->orders->createOrder($this->order->getPickUpDate(), 'test', $user_id, $this->order->getActionId());
        if($order != null){
            // uložit položky objednávky
            foreach($this->getItems() as $item){
                $this->order_fields->createOrderField($item->id, $order->id, $item->count);
            }

            // odstraníme objednávkovou session
            $this->order = null;

            return true;
        }
        throw new Exception("Error při ukládání objednávky do DB");
    }
}