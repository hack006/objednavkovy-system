<?php
use \Nette\Application\UI\Form;
use \Grido\Grid;
use \ResSys\ImageFactory;

class OrderPresenter extends BasePresenter{

    /**
     * @var \ResSys\ActionModel
     */
    private $actions;

    /**
     * @var \ResSys\ProductModel
     */
    private $products;

    /**
     * @var \ResSys\OrderModel
     */
    private $orders;

    /**
     * @var \ResSys\OrderFieldModel
     */
    private $order_fields;

    /**
     * @var \ResSys\FetchHourModel
     */
    private $fetch_hours;

    /**
     * Injektování potřebných služeb - DB
     *
     * @param ResSys\ActionModel $actions
     * @param ResSys\ProductModel $products
     * @param ResSys\OrderModel $orders
     * @param ResSys\OrderFieldModel $order_fields
     */
    public function inject(\ResSys\ActionModel $actions, \ResSys\ProductModel $products, \ResSys\OrderModel $orders,
                           \ResSys\OrderFieldModel $order_fields, \ResSys\FetchHourModel $fetch_hours){
        $this->actions = $actions;
        $this->products = $products;
        $this->orders = $orders;
        $this->order_fields = $order_fields;
        $this->fetch_hours = $fetch_hours;
    }

    public function renderDefault(){
        $this->mustBeSignedIn();

        $this->template->title = "Přehled objednávek";
        // @TODO vypsat dosavadni objednavky uzivatele
        $this->template->user_orders = $this->orders->getUserOrders($this->user->getId());
    }

    public function createComponentUserOrdersGrid(){
        $grid = new \Grido\Grid($this, 'userOrdersGrid');
        $grid->setModel($this->orders->getUserOrders($this->user->getId()));
        $grid->addColumnNumber('id', '#ID');
        $grid->addColumnDate('order_time', 'Objednáno', 'Y-m-d H:i')
            ->setSortable();
        $grid->addColumnDate('fetch_time', 'Datum vyzvednutí', 'Y-m-d H:i')
            ->setSortable();
        $grid->addColumnText('status', 'Stav vyřízení')
            ->setSortable(true)
            ->setCustomRender($this->gridStateRenderer);

        $translator_cs = new \Grido\Translations\FileTranslator('cs');
        $grid->setTranslator($translator_cs);
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
    public function gridStateRenderer(\Nette\Database\Table\ActiveRow $row){
        switch($row->status){
            case 'new':
                return 'čeká na vyřízení';
            case 'accepted':
                return 'zarezervováno';
            case 'canceled':
                return 'zrušeno';
            case 'done':
                return 'připraveno';
            case 'pickedup':
                return 'vyzvednuto';
            default:
                return 'neznámý';
        }
    }

    public function renderSelectOrderPickUp(){
        if(!$this->user->isLoggedIn()){
            $this->flashMessage('Pro dokončení objednávky je třeba se přihlásit abychom měli k dispozici
            Vaše kontaktní údaje. V případě, že u nás účet nemáte, pak se
            <a href="'.$this->link('Sign:register').'" title="Zobrazí registrační formulář.">ZAREGISTRUJTE.</a>', 'alert');
            $this->redirect('Sign:in');
        }
        $this->template->title = 'Objednávka - volba času odběru (krok 2/3)';
    }

    public function renderOrderSummary(){
        // TODO dodelat styl pro tisk!
        $this->mustBeSignedIn();
        // kontrola, zda vše validní
        if(!isset($this->getSession("user_space")->current_order)){
            $this->flashMessage('Nelze zobrazit přehled objednávky, jelikož aktuálně nemáte žádnou vytvořenu.', 'error');
            $this->redirect('Homepage:default');
        }
        else{
            // uživatel může zkusit objednat bez vybraného produktu!
            $order = $this->getSession("user_space")->current_order;
            if($order->getPickUpDate() == null){
                $this->flashMessage('Před dokončením objednávky je nutné vyplnit datum a čas vyzvednutí!', 'error');
                $this->redirect('Order:selectOrderPickUp');
            }
        }
        $order_p = new \ResSys\OrderProxy($this->context->getService('database'), $this->getSession("user_space")->current_order);

        $this->template->title = 'Objednávka - potvrzení (krok 3/3)';
        $this->template->order = $order_p->getOrder();
        $this->template->action = $this->actions->findAll()->get($order_p->getOrder()->getActionId());
        $this->template->user_info = $this->users->findAll()->get($this->user->getId());
        $this->template->order_price_total = $order_p->getOrder()->getTotalPrice();

        $this->template->product_images = array();
        // ziskani url nahledu
        foreach($order_p->getOrder()->getProducts() as $product){
            // pokusit se načíst obrázek, pokud existuje
            try{
                $this->template->product_images[$product->id] = ImageFactory::getProductImage($product->id, ImageFactory::THUMB);
            }
            catch(Exception $e){
                // raději vykreslíme obrázek not-available, než rozbíjet layout chybějící výplní
                $this->template->product_images[$product->id] = 'images/not-available_'.ImageFactory::THUMB.".jpg";
            }
        }
    }

    public function renderDetail(){
        // TODO css pro tisk
        $this->mustBeSignedIn();
        $order_id = $this->getParameter('id');
        if(!$this->orders->isOwner($order_id, $this->user->getId())){
            $this->flashMessage('Nemáte oprávnění nahlížet na detail této objednávky!', 'error');
            $this->redirect('Order:default');
        }
        $order =  $this->orders->findAll()->get($order_id);

        $this->template->title = 'Detail objednávky: #'.$order->id;
        $this->template->order = $order;
        $this->template->order_price_total = $this->orders->getTotalPrice($order_id);
        $this->template->order_fields = $this->order_fields->getOrderFields($order_id);

        $this->template->product_images = array();
        // ziskani url nahledu
        foreach($this->template->order_fields as $product){
            // pokusit se načíst obrázek, pokud existuje
            try{
                $this->template->product_images[$product->product_id] = ImageFactory::getProductImage($product->product_id, ImageFactory::THUMB);
            }
            catch(Exception $e){
                // raději vykreslíme obrázek not-available, než rozbíjet layout chybějící výplní
                $this->template->product_images[$product->product_id] = 'images/not-available_'.ImageFactory::THUMB.".jpg";
            }
        }
    }

    public function createComponentCalendar(){
        $order_p = new \ResSys\OrderProxy($this->context->getService('database'), $this->getSession("user_space")->current_order);

        $current_month = date('n', time());
        $current_year = date('Y', time());
        $c = new CalendarControl($current_month, $current_year);
        // zvýrazníme vybrané datum vyzvednutí pokud je k dispozici
        if(($pud = $order_p->getPickUpDate()) != null){
            list($year, $month, $day) = explode(':', date('Y:m:d:', $pud->getTimestamp()));
            $c->setSelectedDay($day, $month, $year);
        }
        // registrujeme obslužnou rutinu
        $c->onDaySelect[] = $this->calendarDaySelectHandler;
        $c->setGetDayContentFunc($this->getDayContent);
        return $c;
    }

    public function calendarDaySelectHandler($day,$month,$year){
        // uložit do session
        $order_p = new \ResSys\OrderProxy($this->context->getService('database'), $this->getSession("user_space")->current_order);
        $order_p->setPickUpDate(new \Nette\DateTime($year.'-'.$month.'-'.$day));

        $this->getSession("user_space")->current_order;
    }

    public function createComponentTimeForm(){
        $order_p = new \ResSys\OrderProxy($this->context->getService('database'), $this->getSession("user_space")->current_order);

        $renderer = new \Kdyby\BootstrapFormRenderer\BootstrapRenderer();
        $form = new \Nette\Application\UI\Form();
        $form->addProtection();
        $form->setRenderer($renderer);

        $form_time = $form->addText('time', 'Čas odběru:')
            ->addRule('DateValidators::timeValidator', "Zadali jste neplatný čas! Dodržte prosím formát hh:mm.")
            ->setRequired()
            ->setType('time');
        // nastavit zvolený čas pokud ho máme již k dispozici
        if($order_p->getOrder()->getPickUpDate() != null &&
            ($time = $order_p->getOrder()->getPickUpDate()->format('H:i')) != '00:00'){

            $form_time->setValue($time);
        }

        $form->addSubmit('potvrdit', 'Potvrdit')
            ->setOption('class', 'btn-primary pull-right');
        $form->onSuccess[] = $this->timeFormSubmitted;

        return $form;
    }

    public function timeFormSubmitted(\Nette\Application\UI\Form $form){
        $this->mustBeSignedIn();
        // TODO doplnit cas do objednavky
        // TODO overit, ze cas je platny s ohledem na fetch_hours
        $values = $form->getValues();

        $order_p = new \ResSys\OrderProxy($this->context->getService('database'), $this->getSession("user_space")->current_order);
        $pud = $order_p->getPickUpDate();
        // pricist k datu navoleny cas
        list($hour, $minute) = explode(':', $values['time']);
        $pud->setTime($hour, $minute);

        // otestovat cas na prunik s hodinami k vyzvednuti
        if($this->fetch_hours->isValidFetchTime($order_p->getOrder()->getActionId(), $pud)){
            $this->redirect('Order:orderSummary');
        }
        else{
            $this->flashMessage('V zadaný čas nelze zboží vyzvednout. Prosíme, zvolte čas dle kalendáře, kdy jsme Vám k dispozici!', 'error');
            //$this->redirect('Order:selectOrderPickUp');
        }


    }

    public function getDayContent($day, $month, $year){
        $order = $this->getSession("user_space")->current_order;
        $action_date = $year.'-'.$month.'-'.$day;
        $fetch_hours = $this->fetch_hours->findAll()
            ->where('action_id', $order->getActionId())
            ->where('date', $action_date);
        /**
         * class: (string) Třída aplikovaná na celou buňku
         *          selectable: (bool) [required] Pokud TRUE je vytvořen odkaz, jinak není
         *          items:
         *              from: (timestamp) Odkdy platí
         *              until: (timestamp) Dokdy platí
         *              text: (string) [required] Popisný text
         *              class: (string)
         */
        if(mktime(0,0,0,$month,$day+1,$year) < time()){
            // objednávka do minulosti nemá smysl
            $ret = array('selectable' => false, 'class' => 'in-the-past', 'items' => array());
        }
        elseif($fetch_hours->count() > 0){
            $ret = array('selectable' => true, 'class' => 'order-possible', 'items' => array());
            foreach($fetch_hours as $fh){
                $from_timestamp = mktime($fh->time_from->h, $fh->time_from->d, 0, $month, $day, $year);
                $until_timestamp = mktime($fh->time_until->h, $fh->time_until->d, 0, $month, $day, $year);
                $ret['items'][] = array('from' => $from_timestamp, 'until' => $until_timestamp, 'text' => 'otevřeno');
            }
        }
        else{
            $ret = array('selectable' => false, 'class' => '', 'items' => array());
        }

        return $ret;
    }

    public function actionConfirmOrder(){
        $this->mustBeSignedIn();
        $order_p = new \ResSys\OrderProxy($this->context->getService('database'), $this->getSession("user_space")->current_order);

        $order_p->confirmOrder($this->user->getId());

        $this->flashMessage('Objednávka úspěšně vytvořena.', 'success');
        $this->redirect('Order:');
    }

    /* Továrničky pro drobečkovou navigaci */

    /**
     * Továrnička drobečkové navigace - krok 2
     *
     * @return BreadCrumbControl
     */
    public function createComponentStep2Breadcrumb(){
        $b = new BreadCrumbControl();
        $b->addLink('Domů', $this->link('Homepage:'));
        $b->addLink('Nová objednávka');
        $b->addLink('Volba datumu');

        return $b;
    }

    /**
     * Továrnička drobečkové navigace - krok 3
     *
     * @return BreadCrumbControl
     */
    public function createComponentStep3Breadcrumb(){
        $b = new BreadCrumbControl();
        $b->addLink('Domů', $this->link('Homepage:'));
        $b->addLink('Nová objednávka');
        $b->addLink('Volba datumu', $this->link('Order:selectOrderPickUp'));
        $b->addLink('Potvrzení');

        return $b;
    }

    /**
     * Továrnička drobečkové navigace - detail objednávky
     *
     * @return BreadCrumbControl
     */
    public function createComponentDetailBreadcrumb(){
        $b = new BreadCrumbControl();
        $b->addLink('Domů', $this->link('Homepage:'));
        $b->addLink('Moje objednávky', $this->link('Order:'));
        $b->addLink('Detail objednávky');

        return $b;
    }

    /**
     * Továrnička drobečkové navigace - souhrn objednávek
     *
     * @return BreadCrumbControl
     */
    public function createComponentDefaultBreadcrumb(){
        $b = new BreadCrumbControl();
        $b->addLink('Domů', $this->link('Homepage:'));
        $b->addLink('Moje objednávky');

        return $b;
    }
}