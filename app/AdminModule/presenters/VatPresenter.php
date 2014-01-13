<?php
/**
 * Objednávkový systém (https://github.com/hack006/objednavkovy-system)
 *
 * Copyright (C) 2014 Ondřej Janata (o.janata[at]gmail.com)
 */

namespace AdminModule;

class VatPresenter extends \AdminModule\BasePresenter{

    /**
     * Defaultní akce, zobrazí výpis dostupných daňových sazeb
     */
    public function renderDefault() {
        $this->template->title = 'Daňové sazby';
        $this->template->vats = $this->vats->findAll();

    }
}