<?php
/**
 * Objednávkový systém (https://github.com/hack006/objednavkovy-system)
 *
 * Copyright (C) 2014 Ondřej Janata (o.janata[at]gmail.com)
 */

namespace AdminModule;
use Grido\Grid;
use \ResSys\ImageFactory;

class OrderPresenter extends BasePresenter{

    /**
     * @var \ResSys\OrderModel
     */
    private $orders;

    /**
     * @var \ResSys\OrderFieldModel
     */
    private $order_fields;

    /**
     * Předává presenteru zdroje
     *
     * @param \ResSys\OrderModel $order_model
     * @param \ResSys\OrderFieldModel $order_field_model
     */
    public function inject(\ResSys\OrderModel $order_model, \ResSys\OrderFieldModel $order_field_model){
        $this->orders = $order_model;
        $this->order_fields = $order_field_model;
    }

    /**
     * Defaultní akce presenteru, zobrazí výpis všech objednávek do datagridu
     */
    public function renderDefault(){
        $this->mustBeAdmin();

    }

    /**
     * Továrnička na vytvoření datagridu pro objednávky
     *
     * @return \Grido\Grid
     */
    public function createComponentOrderGrid(){
        $grid = new Grid($this, 'orderGrid');
        $grid->setModel($this->orders->findAll());
        $translator_cs = new \Grido\Translations\FileTranslator('cs');
        $grid->setTranslator($translator_cs);
        $grid->setDefaultSort(array('id' => 'desc'));

        $grid->addColumnNumber('id', '#ID');
        $grid->addColumnDate('order_time', 'Objednáno', 'Y-m-d H:i')
            ->setSortable();
        $grid->addColumnDate('fetch_time', 'Datum vyzvednutí', 'Y-m-d H:i')
            ->setSortable();
        $grid->addColumnText('status', 'Stav vyřízení')
            ->setSortable(true)
            ->setCustomRender($this->gridStateRenderer);

        $grid->addActionHref('detail', 'Zobrazit detail objednávky')
            ->setIcon('eye-open');

        return $grid;
    }

    /**
     * Vlastní renderer pro lokalizaci anglických stavů použitých v DB
     *
     * @param \Nette\Database\Table\ActiveRow $row Databázový řádek, ze kterého budeme vykreslovat
     * @return string Lokalizovaný popisek stavu objednávky
     */
    public static function gridStateRenderer(\Nette\Database\Table\ActiveRow $row){
        switch($row->status){
            case 'new':
                return '<span class="label">čeká na vyřízení</span>';
            case 'accepted':
                return '<span class="label label-warning">zarezervováno</span>';
            case 'canceled':
                return '<span class="label label-important">zrušeno</span>';
            case 'done':
                return '<span class="label label-info">připraveno</span>';
            case 'pickedup':
                return '<span class="label label-success">vyzvednuto</span>';
            default:
                return 'neznámý';
        }
    }

    /**
     * Akce pro zobrazení detailu objednávky
     */
    public function renderDetail(){
        $this->mustBeAdmin();

        $order_id = $this->getParameter('id');

        $order =  $this->orders->findAll()->get($order_id);
        if($order === false){
            $this->flashMessage('Bohužel objednávka s daným #ID neexistuje', 'error');
            $this->redirect('AdminModule:Order:default');
        }

        // zjistit kolik času zbývá do vyzvednutí
        $this->template->in_the_past = true;
        $time_now = new \DateTime('now');
        $remain_time = $time_now->diff(new \DateTime($order->fetch_time));
        if($remain_time->invert == 0){
            $this->template->remain_time = $remain_time->format('%a dnů, %h hod, %i min');
            $this->template->in_the_past = false;
        }

        $this->template->title = 'Detail objednávky: #'.$order->id;
        $this->template->order = $order;
        $this->template->order_price_total = $this->orders->getTotalPrice($order_id);
        $this->template->order_fields = $this->order_fields->getOrderFields($order_id);
        $this->template->customer = $this->users->findAll()->get($order->user_id);

        $this->template->product_images = array();
        // ziskani url nahledu
        foreach($this->template->order_fields as $product){
            // pokusit se načíst obrázek, pokud existuje
            try{
                $this->template->product_images[$product->product_id] = ImageFactory::getProductImage($product->product_id, ImageFactory::THUMB);
            }
            catch(\Nette\FileNotFoundException $e){
                // raději vykreslíme obrázek not-available, než rozbíjet layout chybějící výplní
                $this->template->product_images[$product->product_id] = 'images/not-available_'.ImageFactory::THUMB.".jpg";
            }
        }
    }

    /**
     * Akce pro změnu stavu objednávky na přijatý
     *
     * @param int $order_id Id objednávky
     */
    public function actionAccept($order_id){
        $this->mustBeAdmin();

        $this->orders->changeOrderStatus($order_id, \ResSys\OrderModel::ORDER_ACCEPTED);

        $this->flashMessage('Objednávka přijata', 'success');
        $this->redirect('Order:', array('id' => $order_id));
    }

    /**
     * Akce pro změnu stavu objednávky na vyrobeno
     *
     * @param int $order_id Id objednávky
     */
    public function actionDone($order_id){
        $this->mustBeAdmin();

        $this->orders->changeOrderStatus($order_id, \ResSys\OrderModel::ORDER_DONE);

        $this->flashMessage('Objednávka úspěšně nastavena k vyzvednutí.', 'success');
        $this->redirect('Order:', array('id' => $order_id));
    }

    /**
     * Akce pro změnu stavu objednávky na vyzvednuto
     *
     * @param int $order_id Id objednávky
     */
    public function actionPickedUp($order_id){
        $this->mustBeAdmin();

        $this->orders->changeOrderStatus($order_id, \ResSys\OrderModel::ORDER_PICKED_UP);

        $this->flashMessage('Objednávka úspěšně nastavena jako vyzvednutá.', 'success');
        $this->redirect('Order:', array('id' => $order_id));
    }

    /**
     * Akce pro stornování objednávky
     *
     * @param int $order_id Id objednávky
     */
    public function actionCancel($order_id){
        $this->mustBeAdmin();

        $this->orders->changeOrderStatus($order_id, \ResSys\OrderModel::ORDER_CANCELED);

        $this->flashMessage('Objednávka úspěšně stornována.', 'success');
        $this->redirect('Order:', array('id' => $order_id));
    }
}