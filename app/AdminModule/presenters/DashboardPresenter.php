<?php
/**
 * Objednávkový systém (https://github.com/hack006/objednavkovy-system)
 *
 * Copyright (C) 2014 Ondřej Janata (o.janata[at]gmail.com)
 */

namespace AdminModule;

class DashboardPresenter extends BasePresenter{

    /**
     * Defaultní akce presenteru
     */
    public function renderDefault(){
        $this->template->title = 'Dashboard';
    }
}