<?php
namespace ResSys;

class OrderModel extends AbstractModelDB{

    const ORDER_NEW = 0;

    const ORDER_ACCEPTED = 1;

    const ORDER_DONE = 2;

    const ORDER_PICKED_UP = 4;

    const ORDER_CANCELED = 8;

    /**
     * Přidá objednávku do DB
     *
     * @param \DateTime $fetch_time Datum plánovaného vyzvednutí objednávky
     * @param string $note Poznámka
     * @param int $user_id Id autora objednávky
     * @param int $action_id Id akce, ke které objednávka náleží
     * @return bool|int|\Nette\Database\Table\IRow
     */
    public function createOrder(\DateTime $fetch_time, $note, $user_id, $action_id){

        return $this->getTable()->insert(array(
            'order_time' => date(\DateTime::ATOM, time()),
            'fetch_time' => $fetch_time,
            'note' => $note,
            'user_id' => $user_id,
            'action_id' => $action_id
        ));
    }

    public function changeOrderStatus($order_id, $status){
        switch($status){
            case self::ORDER_NEW:
                return;

            case self::ORDER_ACCEPTED:
                return $this->getTable()->where(array('id' => $order_id))->update(array(
                    'status' => 'accepted'
                ));

            case self::ORDER_DONE:
                return $this->getTable()->where(array('id' => $order_id))->update(array(
                    'status' => 'done',
                    'prepared_time' => date(\DateTime::ATOM, time())
                ));

            case self::ORDER_PICKED_UP:
                return $this->getTable()->where(array('id' => $order_id))->update(array(
                    'status' => 'pickedup',
                    'picked_up_time' => date(\DateTime::ATOM, time())
                ));

            case self::ORDER_CANCELED:
                return $this->getTable()->where(array('id' => $order_id))->update(array(
                    'status' => 'canceled'
                ));
        }
    }

    /**
     * Testuje vlastnictví objednávky pro zadaného uživatele
     *
     * @param int $order_id Id objednávky
     * @param int $user_id Id uživatele
     * @return bool TRUE v případě, že uživatel je vlastníkem objednávky, jinak FALSE
     */
    public function isOwner($order_id, $user_id){
        return ($this->getTable()->where('user_id', $user_id)->where('id', $order_id)->count() > 0);
    }

    /**
     * Vrátí všechny objednávky uživatele
     *
     * @param int $user_id Id uživatele
     * @return \Nette\Database\Table\Selection
     */
    public function getUserOrders($user_id){
        return $this->findAll()->where('user_id', $user_id);
    }

    /**
     * Vrátí celkovou cenu objednávky
     *
     * @param int $order_id Id objednávky
     * @return int
     */
    public function getTotalPrice($order_id){
        $res = $this->getTable()->get($order_id)->related('order_fields')
            ->select('(count * price_without_tax * (1 + vat / 100)) AS total_price_vat_included');
        $sum = 0;
        foreach($res as $item){
            $sum += $item->total_price_vat_included;
        }
        return $sum;
    }
}