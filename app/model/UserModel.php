<?php
namespace ResSys;

class UserModel extends AbstractModelDB{

    public function isUniqueName($username){
        return ($this->getTable()->where('username', $username)->count() <= 0);
    }

    /**
     * Vytvoří nového uživatele
     *
     * @param string $firstname
     * @param string $lastname
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $phone
     * @param string $city
     * @param string $role
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