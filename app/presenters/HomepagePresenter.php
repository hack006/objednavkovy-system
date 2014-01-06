<?php
use \ResSys\ImageFactory;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
    private $actions;

    private $products;

    public function inject(\ResSys\ActionModel $actions, \ResSys\ProductModel $products){
        $this->actions = $actions;
        $this->products = $products;
    }

    /**
     * Defaultní akce - zobrazení homepage
     */
    public function renderDefault()
	{
        // načtení ze session pouze při prvotním načtením, dále ajaxem přidáváno napřímo
        if(!isset($this->template->current_order)){
            $this->template->current_order = $this->getSession("user_space")->current_order;
        }
        // vypisujeme pouze akce, které jsou časově platné
		$this->template->actions = $this->actions->getAllTimeValid(time());

        $this->template->action_order_possible = array();
        $this->template->action_products = array();
        $this->template->product_images = array();

        foreach($this->template->actions as $action){
            $products = $this->actions->getActionProducts($action->id);
            $this->template->action_products[$action->id] = $products;
            if($action->order_from->getTimestamp() < time() && $action->order_until->getTimestamp() > time()){
                $this->template->action_order_possible[$action->id] = true;
            }
            else{
                $this->template->action_order_possible[$action->id] = false;
            }
            // ziskani url nahledu
            foreach($products as $product){
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
	}

    /**
     * Přidání produktu do objednávky
     *
     * @param $product_id Identifikátor produktu, který chceme přidat
     */
    public function handleAddProductToOrder($product_id){
        // @TODO zkontrolovat, zda ještě můžeme přidávat
        $order_p = new \ResSys\OrderProxy($this->context->getService('database'), $this->getSession("user_space")->current_order);

        // přidat produkt do objednávky
        $order_p->addItem($product_id, 1);

        // pokud neexistuje session, založit
        if(!isset($this->getSession("user_space")->current_order)){
            $this->getSession("user_space")->current_order = $order_p->getOrder();
        }

        if(!$this->isAjax()){
            $this->redirect("Homepage:default");
        }

        // AJAX routines
        $this->payload->product_id = $product_id; // použito k zvýraznění přidání dané položky
        $this->template->current_order = $order_p->getOrder(); // @TODO Optimalizovat zasílání pouze části produktů
        $this->redrawControl('sidebar');
    }

    /**
     * Odebrání daného počtu produktu z objednávky
     *
     * V případě odebrání všech nebo více produktů než je k dispozici, dojde k odstranění celého produktu z objednávky.
     *
     * @param $product_id Id produktu
     * @param $count Počet kusů k odebrání
     */
    public function handleRemoveProductFromOrder($product_id, $count){
        // @TODO zkontrolovat, zda ještě můžeme odebírat
        $order_p = new \ResSys\OrderProxy($this->context->getService('database'), $this->getSession("user_space")->current_order);

        try{
            $order_p->removeItem($product_id, $count);
        }
        catch(\Nette\InvalidArgumentException $e){
            $this->error("Při odebírání nastal neočekávaný problém!");
        }

        if(!$this->isAjax()){
            $this->redirect("Homepage:default");
        }

        // AJAX routines
        $this->payload->product_id = $product_id; // použito k zvýraznění přidání dané položky
        $this->template->current_order = $order_p->getOrder(); // @TODO Optimalizovat zasílání pouze části produktů
        $this->redrawControl('sidebar');

    }

    /**
     * Zobrazení detailu produktu
     *
     * @param $product_id Id produktu
     */
    public function handleViewProductDetail($product_id){
        $p = $this->products->findAll()
            ->select('products.*, vats.name AS vat_name, vats.value AS vat_value')
            ->get($product_id);
        $this->template->product_detail = $p;
        $this->template->price_with_tax = $p->price_without_tax * (1 + $p->vat_value / 100);

        // pokusit se načíst obrázek, pokud existuje
        try{
            $img_file = ImageFactory::getProductImage($product_id, ImageFactory::DETAIL);
        }
        catch(Exception $e){
            // raději vykreslíme obrázek not-available, než rozbíjet layout chybějící výplní
            $img_file = 'images/not-available_'.ImageFactory::DETAIL.".jpg";
        }
        $this->template->img = $img_file;

        $this->redrawControl('productdetail');
    }

}
