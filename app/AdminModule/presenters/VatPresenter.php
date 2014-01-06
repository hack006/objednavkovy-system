<?php
namespace AdminModule;

class VatPresenter extends \AdminModule\BasePresenter{

    public function renderDefault() {
        $this->template->title = 'Daňové sazby';
        $this->template->vats = $this->vats->findAll();

    }
}