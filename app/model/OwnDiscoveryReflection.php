<?php
namespace ResSys;

/**
 * Třída pro upravenou konvenci automatického provazování tabulek
 *
 * Např. tabulka cars, tabulka owners
 *  -> propojení je realizováno přes klíč owner_id v tabulce cars
 */
class OwnDiscoveryReflection extends \Nette\Database\Reflection\DiscoveredReflection{

    /**
     * @param $table Tabulka, na kterou chceme připojovat
     * @param $key Název klíče v dané tabulce
     * @param bool $refresh
     * @return array Vrací tabulku a název cizího klíče na ni pro propojení
     */
    public function getBelongsToReference($table, $key, $refresh = TRUE)
    {
        // ziskat pripojovanou tabulku
            $ret_table = explode('.', $key)[0];
        // pokud key končí s, odstranit (množné čislo, klíče pouze jednotným)
        if(substr($key,-1) == 's')
            $ret_key = substr($key,0,-1).'_id';
        else
            $ret_key = $key.'_id';
        return array($ret_table, $ret_key);
    }


}