<?php

namespace AdminModule;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends \Nette\Application\UI\Presenter
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

    /**
     * Zjistí, zda uživatel přihlášen, pokud není redirect na login
     */
    protected function mustBeSignedIn(){
        // uživatel musí být přihlášen
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Sign:in');
        }
    }

    /**
     * Zjistí, zda uživatel má uživatelskou roli admin
     */
    protected function mustBeAdmin(){
        $this->mustBeSignedIn();
        $roles = $this->getUser()->getRoles();
        if(!in_array('admin', $roles)){
            $this->flashMessage('Pro danou akci nemáte dostatečné oprávnění!', 'error');
            $this->redirect(':Homepage:default');
        }
    }

    public function startup(){
        parent::startup();

    }

}
