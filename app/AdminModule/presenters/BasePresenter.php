<?php
/**
 * Objednávkový systém (https://github.com/hack006/objednavkovy-system)
 *
 * Copyright (C) 2014 Ondřej Janata (o.janata[at]gmail.com)
 */

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

    /**
     * Metoda spouštící se před vykreslovací fází, ověřuje pro celou administraci, že uživatel má admin oprávnění.
     * Pokud nemá udělá redirect na přihlašovací stránku.
     */
    public function beforeRender(){
        parent::beforeRender();

        // v administraci se smí pohybovat pouze admin!
        $this->mustBeAdmin();
    }

}
