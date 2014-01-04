<?php
namespace ResSys;

class VatModel extends AbstractModelDB{

    public function getHTMLSelectPairs(){
        return $this->findAll()->fetchPairs('id', 'name');
    }

    public function getDefault(){
        return $this->findAll()->where('default = ?', 'true')->limit(1)->fetch();
    }

}