<?php

/**
 * Třída vlastních validátorů
 *
 * @author Ondřej Janata
 */
class DateValidators{

    /**
     * Validátor pro ověření validity zadaného data
     *
     * @param $date string Ověřované datum. Doporučený formát DATE_W3C - (Y-m-d H:i:sP)
     * @return bool Vrací true v případě platného datumu
     */
    public static function dateValidator($date)
    {
        try {
            $check = new DateTime($date);

        } catch(Exception $e){
            return false;
        }

        return true;
    }

    /**
     * Validátor pro ověření validity času
     *
     * @param $time \Nette\Forms\Controls\TextInput Čas k validaci
     * @return bool Vrací true v případě validného času, jinak false
     */
    public static function timeValidator($time){
        // musíme počítat s jakýmkoliv vstupem
        $values = explode(':', $time->getValue());
        if(count($values) < 2){
            return false;
        }
        else{
            list($hours, $minutes) = $values;
        }

        if(isset($hours) && ctype_digit($hours) && isset($minutes) && ctype_digit($minutes)
            && $hours >= 0 && $hours <= 23 && $minutes >= 0 && $minutes <= 59){

            return true;
        }
        return false;
    }
}