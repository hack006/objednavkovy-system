<?php

use Nette\Application\UI;


/**
 * Sign in/out presenters.
 */
class ProductPresenter extends BasePresenter
{

    /**
     * Defaultní akce
     *
     * @return string
     */
    function defaultRender() {
        return "product_presenter";
    }

}
