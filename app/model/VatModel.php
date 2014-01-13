<?php
namespace ResSys;

class VatModel extends AbstractModelDB{

    /**
     * Vrátí asociativní pole s klíčem v podobě id daně a hodnotou názvem daňové sazby
     *
     * @return array
     */
    public function getHTMLSelectPairs(){
        return $this->findAll()->fetchPairs('id', 'name');
    }

    /**
     * Pokud je nastaveno, vrátí defaultní daň
     *
     * @return bool|mixed|IRow
     */
    public function getDefault(){
        return $this->findAll()->where('default = ?', 'true')->limit(1)->fetch();
    }

}