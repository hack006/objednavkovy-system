<?php
namespace ResSys;

class UserModel extends AbstractModelDB{

    public function isUniqueName($username){
        return ($this->getTable()->where('username', $username)->count() <= 0);
    }

    /**
     * Vytvoří nového uživatele
     *
     * @param string $firstname Křestní jméno
     * @param string $lastname Příjmení
     * @param string $username Uživatelské jméno
     * @param string $password Heslo
     * @param string $email Email
     * @param string $phone Telefon
     * @param string $city Město bydliště
     * @param string $role Role v systému
     */
    public function createUser($firstname, $lastname, $username, $password, $email, $phone, $city, $role){
        // TODO osolit heslo
        $salted_password = $password;

        return $this->getTable()->insert(array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'username' => $username,
            'password' => $salted_password,
            'email' => $email,
            'phone' => $phone,
            'city' => $city,
            'role' => $role
        ));
    }
}