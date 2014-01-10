<?php
use \Nette\Application\UI;

/**
 * Jednoduchy kalendar
 *
 * @author Ondřej Janata
 */
class CalendarControl extends UI\Control
{
    /** @persistent int */
    public $month;

    /**
     * @var int Číselná reprezentace roku
     * @persistent
     */
    public $year;

    /**
     * @var int Vybraný den v kalendáři
     * @persistent
     */
    public $sel_day;

    /**
     * @var int Vybraný měsíc v kalendáři
     * @persistent
     */
    public $sel_month;

    /**
     * @var int Vybraný rok v kalendáři
     * @persistent
     */
    public $sel_year;

    /**
     * @var array Pole callback funkcí s parametry (den,měsíc,rok), které budou volány po kliknutí na určitý den.
     */
    public $onDaySelect = array();

    /**
     * @var Funkce s 3 paramtery (den,měsíc,rok), která vrátí html kód pro danou buňku.
     */
    private $get_day_html_content_func;

    /**
     * @param int $month Měsíc
     * @param int $year Rok
     */
    public function __construct($month, $year){
        parent::__construct();

        $this->month = $month;
        $this->year = $year;
        $this->setGetDayContentFunc($this->defaultGetDayContentFunc);
    }

    /**
     * Nastaví vybraný den v kalendáři
     * @param $day Den
     * @param $month Měsíc
     * @param $year Rok
     */
    public function setSelectedDay($day, $month, $year){
        if(!checkdate($month, $day, $year)){
            // přenastavit datum, které zobrazuje kalendář na aktuální
            $this->month = date('n', time());
            $this->year = date('Y', time());
            throw new \Nette\InvalidArgumentException('Zadané parametry nepředstavují platné datum!');
        }

        $this->sel_day = $day;
        $this->sel_month = $month;
        $this->sel_year = $year;
    }

    /**
     * @param $function Funkce, která se bude volat pro naplnění obsahu buňky kalendáře.
     * Předávané parametry (den,měsíc,rok). Funkce musí vracet pole v následujícím formátu:
     *      <pre>
     *          class: (string) Třída aplikovaná na celou buňku
     *          selectable: (bool) [required] Pokud TRUE je vytvořen odkaz, jinak není
     *          items:
     *              from: (timestamp) Odkdy platí
     *              until: (timestamp) Dokdy platí
     *              text: (string) [required] Popisný text
     *              class: (string)
     *      </pre>
     * @throws InvalidArgumentException Pokud nejde validní funkci
     */
    public function setGetDayContentFunc($function){
        if(!is_callable($function)){
            throw new InvalidArgumentException('Parametr není volatelná funkce!');
        }

        $this->get_day_html_content_func = $function;
    }

    public function render(){
        // kontrola validity předávaných parametrů specifikujících zobrazovaný měsíc
        if($this->month < 1 || $this->month > 12 || $this->year < 1971 || $this->year > 2035){
            // opravit hodnoty na současný měsíc
            $this->month = date('n', time());
            $this->year = date('Y', time());

            $this->flashMessage("Zadán neexistující měsíc! Opraveno na aktuální měsíc.", 'error');
        }

        $this->template->headings = array('Neděle','Pondělí','Úterý','Středa','Čtvrtek','Pátek','Sobota');
        $this->template->month = $this->month;
        $this->template->year = $this->year;
        $this->template->running_day = date('w',mktime(0,0,0,$this->month,1,$this->year));
        $this->template->days_in_month = date('t',mktime(0,0,0,$this->month,1,$this->year));
        if($this->sel_month == $this->month && $this->sel_year == $this->year && is_numeric($this->sel_day)){
            $this->template->selected_day = $this->sel_day;
        }
        $this->template->items = array();
        for($i=1; $i <= $this->template->days_in_month; $i++){
            $this->template->items[$i] = $this->getDayContent($i, $this->template->month, $this->template->year);
        }

        $this->template->setFile(__DIR__.'/calendar.latte');
        $this->template->render();
    }

    /**
     * Obslužná rutina pro zachycení události výběru data v kalendáři
     *
     * @param $day int Vybraný den
     * @param $month int Vybraný měsíc
     * @param $year int Vybraný rok
     */
    public function handleClick($day, $month, $year){
        try{
            $this->setSelectedDay($day, $month, $year);
        }
        catch(\Nette\InvalidArgumentException $e){
            $this->flashMessage($e->getMessage(), "error");
            return;
        }
        $this->redrawControl('calendarbody');

        // voláme registrované posluchače
        foreach($this->onDaySelect as $calback){
            $calback($day, $month, $year);
        }
    }

    public function handleNextMonth(){
        // přechod do následujícího roku
        if($this->month == 12){
            $this->month = 1;
            $this->year++;
        }
        else{
            $this->month++;
        }
        $this->redrawControl('calendarcontrol');
        $this->redrawControl('calendarbody');
    }

    public function handlePreviousMonth(){
        // přechod do předchozího
        if($this->month == 1){
            $this->month = 12;
            $this->year--;
        }
        else{
            $this->month--;
        }
        $this->redrawControl('calendarcontrol');
        $this->redrawControl('calendarbody');
    }

    public function getDayContent($day, $month, $year){
        return $this->get_day_html_content_func->__invoke($day,$month,$year);
    }

    public function defaultGetDayContentFunc($day, $month, $year){
        return array('class' => 'default', 'selectable' => true);
    }
}