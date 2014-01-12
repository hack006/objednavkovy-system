<?php
/**
 * Objednávkový systém (https://github.com/hack006/objednavkovy-system)
 *
 * Copyright (C) 2014 Ondřej Janata (o.janata[at]gmail.com)
 */

namespace AdminModule;

use \Nette\Application\UI;
use \Nette\Application\UI\Form;
use \Nette\Utils\Html;


/**
 * Sign in/out presenters.
 */
class ProductPresenter extends \AdminModule\BasePresenter
{
    /** @var  Product DB model */
    private $products;


    public function __construct(\ResSys\ProductModel $products)
    {
        $this->products = $products;
    }

    /**
     * Akce výpis produktů
     */
    public function renderDefault() {
        $this->mustBeAdmin();

        $this->template->title = 'Produkty';
        $this->template->posts = $this->products->findAll();

    }

    /**
     * Akce nový produkt
     */
    public function renderNew(){
        $this->mustBeAdmin();
    }

    /**
     * Funkce zpracovávající odeslaná data z formuláře a ukládající je do DB
     */
    public function productFormSubmitted(Form $form){
        $this->mustBeAdmin();

        $this->products->createProduct($form->values->name, $form->values->description, $form->values->price_without_tax,
            $form->values->min_order_time_hours, $form->values->min_order_time_days, $form->values->vat_id);

        $this->flashMessage('Produkt přidán.', 'success');
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $form->setValues(null, true);
        $this['productForm']->

        $form->setValues(array('userId' => $form->values->userId), TRUE);
        $this['taskList']->invalidateControl();
    }

    /**
     * Vytvoří komponentu formuláře pro tvorbu a editaci produktu
     */
    protected function createComponentProductForm(){
        $vats = $this->vats->getHTMLSelectPairs();
        $default_vat = $this->vats->getDefault();

        $renderer = new \Kdyby\BootstrapFormRenderer\BootstrapRenderer();
        $form = new \Nette\Application\UI\Form();
        $form->setRenderer($renderer);
        // CSRF protekce pomocí přidaného bezpečnostnímu tokenu
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');

        $form->addText('name', 'Název')
            ->setRequired()
            ->addRule(Form::LENGTH, "Název musí být délky %d až %d znaků.", array(3,255));
        $form->addTextArea('description', 'Popis');
        $form->addText('price_without_tax', 'Cena zboží bez daně')
            ->setRequired()
            ->addRule(Form::FLOAT, "Musí být platné desetinné číslo.")
            ->addRule(Form::PATTERN, "Číslo nesmí být záporné. Zapisujte bez mezer. Desetinný oddělovač \".\".", '^\d*\.?\d*$');
        $form->addSelect('vat_id', 'Použitá daň', $vats)
            ->setPrompt('- Vyberte -')
            ->setDefaultValue($default_vat)
            ->setRequired();
        $form->addText('min_order_time_hours', 'Minimální čas od objednání k možnosti vyzvednutí [hodiny]')
            ->addRule(Form::RANGE, "Povolené jsou pouze hodnoty od %d do %d.", array(0,23));
        $form->addText('min_order_time_days', 'Minimální čas od objednání k možnosti vyzvednutí [dny]')
            ->addRule(Form::PATTERN, "Číslo nesmí být záporné. Zapisujte bez mezer.", '^\d$');
        $form->onSuccess[] = $this->productFormSubmitted;
        $form->addSubmit('create', 'Uložit');

        return $form;
    }

    /**
     * Vytvoří grafickou komponentu pro výpis produktů do přehledné tabulky
     */
    protected function createComponentProductgrid()
    {
        $grid = new \Grido\Grid($this, 'productgrid');
        $grid->setModel($this->context->productModel->findAll()->select('products.*, vats.name AS vatname'));
        $grid->addColumnText('name', 'Název')
            ->setColumn('name')
            ->setSortable();
        $grid->addColumnText('price_without_tax', 'Cena bez daně')
            ->setSortable();
        $grid->addColumnText('vatname', 'Daň')
            ->setColumn('vatname')
            ->setSortable();
        $grid->addColumnText('min_order_time_hours', 'Vyřízení objednávky [hod]')
            ->setSortable();
        $grid->addColumnText('min_order_time_days', 'Vyřízení objednávky [dny]')
            ->setSortable();
        $grid->addActionHref('edit', 'Edit')
            ->setIcon('pencil');

        $grid->addActionHref('delete', 'Delete')
            ->setIcon('trash')
            ->setConfirm(function($item) {
            return "Opravdu chcete smazat výrobek {$item->name}?";});
        $grid->setExport();
        //return $grid;
    }

}