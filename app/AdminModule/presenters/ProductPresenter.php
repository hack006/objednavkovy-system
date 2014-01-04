<?php

namespace AdminModule;

use \Nette\Application\UI;
use \Nette\Application\UI\Form;
use \Nette\Utils\Html;


/**
 * Sign in/out presenters.
 */
class ProductPresenter extends BasePresenter
{
    /** @var  Product DB model */
    private $products;


    public function __construct(\ResSys\ProductModel $products)
    {
        $this->products = $products;
    }

    public function renderDefault() {
        $this->template->posts = $this->products->findAll();

    }

    public function renderNew(){
        // uživatel musí být přihlášen
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Sign:in');
        }
    }

    /**
     * Vytvoří komponentu formuláře pro tvorbu a editaci produktu
     */
    protected function createComponentProductForm(){
        $vats = $this->vats->getHTMLSelectPairs();
        $default_vat = $this->vats->getDefault();

        $form = new \Nette\Application\UI\Form();
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
     * Funkce zpracovávající odeslaná data z formuláře a ukládající je do DB
     */
    public function productFormSubmitted(Form $form){
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

    protected function createComponentProductgrid()
    {
        $grid = new \Grido\Grid($this, 'productgrid');
        $grid->setModel($this->context->productModel->findAll()->select('products.*, vats.name AS vatname'));
        $grid->addColumnText('name', 'Název')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('price_without_tax', 'Cena bez daně')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('vatname', 'Daň')
            ->setColumn('vatname')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('min_order_time_hours', 'Vyřízení objednávky [hod]')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('min_order_time_days', 'Vyřízení objednávky [dny]')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->setExport();
        //return $grid;
    }

}