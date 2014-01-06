<?php
namespace ResSys;

class OrderItem {

    /**
     * @var Id zboží v DB
     */
    private $id;

    /**
     * @var Název
     */
    private $name;

    /**
     * @var Počet kusů
     */
    private $count;

    /**
     * @var Jednotková cena produktu s daní
     */
    private $price_vat_included;

    /**
     * @var Celková cena za položku
     */
    private $total_price_vat_included;

    /**
     * @var array Výčet proměných, které budou dostupné přes "property getter"
     */
    private $_properties = array('id', 'name', 'count', 'price_vat_included', 'total_price_vat_included');

    /**
     * Konstruktor
     *
     * @param $id
     * @param $name
     * @param $count
     * @param $price_vat_included
     */
    function __construct($id, $name, $count, $price_vat_included)
    {
        $this->id = $id;
        $this->name = $name;
        $this->count = $count;
        $this->price_vat_included = $price_vat_included;
        $this->total_price_vat_included = $count * $price_vat_included;
    }

    /**
     * Rutina simulující chování properties. Nemusíme tedy psát gettery.
     *
     * @param $name Název vlastnosti
     * @return mixed Hodnota požadované vlastnosti
     * @throws \Exception
     */
    final function &__get($name)
    {
        if (!in_array($name, $this->_properties)) {
            throw new \Exception("Undefined property '$name'.");
        } else {
            return $this->{$name};
        }
    }

    /**
     * Zvýší počet produktů o daný počet
     *
     * @param $count Počet
     * @throws \Nette\InvalidArgumentException
     */
    public function raiseCount($count){
        if(!is_int($count) || $count < 0){
            throw new \Nette\InvalidArgumentException("Přidat lze pouze nezáporný celočíselný počet položek");
        }
        $this->count += $count;
        $this->recalculate();
    }

    /**
     * Sníží počet produktů o daný počet
     *
     * @param $count Počet
     * @throws \Nette\InvalidArgumentException
     * @throws \Nette\OutOfRangeException
     */
    public function lowerCount($count){
        if(!is_int($count) || $count < 0){
            throw new \Nette\InvalidArgumentException("Odebrat lze pouze nezáporný celočíselný počet položek");
        }
        if($count > $this->count){
            throw new \Nette\OutOfRangeException("Nelze odebrat více produktů než je k dispozici!");
        }
        $this->count -= $count;
        $this->recalculate();
    }

    /**
     * Provede přepočítání cen dle aktuálních hodnot v DB
     */
    public function recalculate(){
        // @TODO implementovat aktualizaci z DB?
        $this->total_price_vat_included = $this->price_vat_included * $this->count;
    }
}