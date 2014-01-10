<?php

class AbstractBasePresenter extends \Nette\Application\UI\Presenter
{
    /**
     * @var \ResSys\UserModel
     */
    protected $users;

    public function injectBase(\ResSys\UserModel $users){
        $this->users = $users;
    }

    /**
     * Zjistí, zda uživatel přihlášen, pokud není redirect na login
     */
    protected function mustBeSignedIn(){
        // uživatel musí být přihlášen
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Sign:in');
        }
    }

    /**
     * Zjistí, zda uživatel má uživatelskou roli admin
     */
    protected function mustBeAdmin(){
        $this->mustBeSignedIn();
        $roles = $this->getUser()->getRoles();
        if(!in_array('admin', $roles)){
            $this->flashMessage('Pro danou akci nemáte dostatečné oprávnění!', 'error');
            $this->redirect(':Homepage:default');
        }
    }
}