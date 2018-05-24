<?php
/**
 * Created by PhpStorm.
 * User: robery567
 * Date: 23/05/2018
 * Time: 17:31
 */

class Module_User_Model {
    /** @var Module_Database_Model */
    protected $DataSource;

    /** @var Module_Tree_Model */
    protected $Tree;

    /**
     * Module_User_Model constructor.
     * @throws Exception
     */
    public function __construct() {
        $this->DataSource = new Module_Database_Model();
        $this->DataSource->setName('hearme_db');
        $this->DataSource->setColumns(['username', 'email', 'password', 'first_name', 'last_name', 'gender', 'avatar', 'online', 'friends']);

        $this->Tree = $this->DataSource->load();
    }

    /**
     * Checks if the given user credentials are valid
     * @param $email
     * @param $password
     * @return bool
     * @throws Exception
     */
    public function checkAuthenticationCredentials($email, $password) {
        if (null !== $this->Tree->find([$email, $password], null, ['email', 'password'])) {
            return true;
        }

        return false;
    }

    /**
     * Gets the user's data by a given key
     * @param $keyName
     * @param $keyVal
     * @return false|Module_Node_Model
     * @throws Exception
     */
    public function getUserBy($keyName, $keyVal) {
        $userData = $this->Tree->find($keyVal, null, $keyName);

        if (null === $userData) {
            return null;
        }

        return $userData->getValue();
    }

    /**
     * Inserts the given user into the database
     * @param $userData
     * @return bool
     * @throws Exception
     */
    public function insertUser($userData) {
        if (!isset($userData['email'], $userData['first_name'], $userData['last_name'], $userData['first_name'], $userData['password'], $userData['gender'])) {
            return -1;
        }

        if (null !== $this->Tree->find($userData['email'], null, 'email')) {
            return false;
        }

        $userData['password'] = md5($userData['password']);
        $userData['friends'] = ['0'];

        return $this->DataSource->insert($userData);
    }

    /**
     * Add an email to the friends list of the user
     * @param $originEmail
     * @param $friendEmail
     * @return bool
     * @throws Exception
     */
    public function addFriend($originEmail, $friendEmail) {
        $friendData = $this->Tree->find($friendEmail, null, 'email');

        if (null === $friendData) {
            return false;
        }

        $foundUser = $this->Tree->find($originEmail, null, 'email');
        $userData = $foundUser->getValue();

        foreach ($userData['friends'] as $friend) {
            if ($friend === $friendEmail) {
                return -1;
            }
        }

        $userData['id'] = $foundUser->getId();

        if (empty($userData)) {
            return false;
        }

        if (empty((int)$userData['friends'][0])) {
            $userData['friends'][0] = $friendEmail;
        } else {
            $userData['friends'][] = $friendEmail;
        }

        return $this->DataSource->update($userData);
    }
}