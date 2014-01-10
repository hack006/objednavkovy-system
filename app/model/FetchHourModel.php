<?php
namespace ResSys;

class FetchHourModel extends AbstractModelDB{
    public function isValidFetchTime($action_id, \DateTime $date){
        $date_string = $date->format('Y-m-d');
        $time_string = $date->format('H:i');
        $res = $this->getTable()
            ->where('actions.id', $action_id)
            ->where('fetch_hours.date', $date_string)
            ->where('fetch_hours.time_from <= ?', $time_string)
            ->where('fetch_hours.time_until >= ?', $time_string);
        return ($res->count() > 0);
    }
}