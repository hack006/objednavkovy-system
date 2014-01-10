<?php

namespace AdminModule;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends \AbstractBasePresenter
{

    /**
     * @var \ResSys\VatModel Selektro databázové tabulky vats
     */
    protected $vats;

    /**
     * Zaregistruje nám požadované služby do všech presenterů
     */
    public function injectBaseModels(\ResSys\VatModel $vat_model){
        $this->vats = $vat_model;
    }

    public function startup(){
        parent::startup();

    }

}
