<?php

use Nette\Application\UI;
use Nette\Forms\Form;


/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{


	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
        $renderer = new \Kdyby\BootstrapFormRenderer\BootstrapRenderer();

		$form = new UI\Form;
        $form->setRenderer($renderer);
		$form->addText('username', 'Uživatelské jméno:')
			->setRequired('Prosím zadejte uživatelské jméno.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Prosím zadejte heslo.');

		$form->addCheckbox('remember', 'Zůstat přihlášen');

		$form->addSubmit('send', 'Přihlásit')
            ->setOption('class', 'btn btn-primary');

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = $this->signInFormSucceeded;
		return $form;
	}


	public function signInFormSucceeded($form)
	{
		$values = $form->getValues();

		if ($values->remember) {
			$this->getUser()->setExpiration('14 days', FALSE);
		} else {
			$this->getUser()->setExpiration('20 minutes', TRUE);
		}

		try {
			$this->getUser()->login($values->username, $values->password);
			$this->redirect('Homepage:');

		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

    public function actionIn(){
        $this->template->title = 'Přihlášení';
    }

	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Byli jste úspěšně odhlášeni z aplikace.');
		$this->redirect('in');
	}

    public function renderRegister(){
        $this->template->title = 'Registrace';


    }

    public function createComponentRegisterForm(){
        $renderer = new \Kdyby\BootstrapFormRenderer\BootstrapRenderer();

        $form = new \Nette\Application\UI\Form();
        $form->setRenderer($renderer);

        $form->addText('firstname', 'Jméno:')
            ->setRequired('Povinné pole!')
            ->addRule(Form::MIN_LENGTH, 'Minimální délka %d znaky!', 2)
            ->addRule(Form::PATTERN, 'Povoleny pouze písmena.', '[\S]+');
        $form->addText('lastname', 'Příjmení:')
            ->setRequired('Povinné pole!')
            ->addRule(Form::MIN_LENGTH, 'Minimální délka %d znaky!', 2)
            ->addRule(Form::PATTERN, 'Povoleny pouze písmena.', '[\S]+');
        $form->addText('city', 'Bydliště (město):')
            ->addRule(Form::PATTERN, 'Povoleny pouze písmena.', '[\S\ ]+');;
        $form->addText('email', 'Email:')
            ->setType('email')
            ->setRequired('Povinné pole!')
            ->addRule(Form::EMAIL, 'Zadejte validní emailovou adresu!');
        $form->addText('phone', 'Telefonní číslo:')
            ->setType('tel')
            ->setRequired('Povinné! Telefon vyžadujeme pro potřebu komunikace s Vámi při obsluze objednávky.')
            ->setOption('placeholder', '111333999')
            ->addRule(Form::PATTERN, 'Telefon zapisujte bez mezer. Předvolbu není nutné uvádět.', '(\+[0-9]{3}){0,1}[0-9]{9}');
        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Povinné pole!')
            ->addRule(Form::MIN_LENGTH, 'Minimální délka %d znaky.', 4)
            ->addRule(Form::PATTERN, 'Povolené jsou pouze písmena, číslice, tečka a podtržítko. Slovo musí začínat písmenem.', '[a-z]+[a-z0-9\._]*');
        $form->addPassword('password', 'Heslo:')
            ->setRequired('Povinné pole!')
            ->addRule(Form::MIN_LENGTH, 'Minimální délka %d znaků.', 6);
        $form->addPassword('password_confirmation', 'Heslo znovu:')
            ->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu shody.')
            ->addRule(Form::EQUAL, 'Hesla se neshodují!', $form['password']);
        $form->addSubmit('odeslat', 'Vytvořit účet')
            ->setOption('class', 'btn btn-primary');
        $form->onValidate[] = $this->validateRegisterForm;
        $form->onSuccess[] = $this->registerFormSubmitted;

        return $form;
    }

    public function validateRegisterForm(\Nette\Application\UI\Form $form){
        $values = $form->getValues();

        if(!$this->users->isUniqueName($values->username)){
            $form['username']->addError('Uživatel s daným jménem již existuje! Zvolte prosím jiné.');
        }
    }

    public function registerFormSubmitted(\Nette\Application\UI\Form $form){
        $val = $form->getValues();
        $salted_password = \ResSys\Authenticator::calculateHash($val->password);
        $this->users->createUser($val->firstname, $val->lastname, $val->username, $salted_password, $val->email,
            $val->phone, $val->city, 'customer');

        $this->flashMessage('Registrace proběhla úspěšně! Nyní se můžete přihlásit.', 'success');
        $this->redirect('Sign:in');
    }

}
