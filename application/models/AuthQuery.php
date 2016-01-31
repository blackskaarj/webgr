<?php
class AuthQuery {
	public static function getUserRole()
	{
		$auth = Zend_Auth::getInstance();
		$storage = $auth->getStorage()->read();
		$const = User::COL_ROLE;
		return $storage->$const;
	}
	
    public static function getUserId()
    {
        $auth = Zend_Auth::getInstance();
        $storage = $auth->getStorage()->read();
        $const = User::COL_ID;
        return $storage->$const;
    }
    
    public static function getUserName()
    {
        $auth = Zend_Auth::getInstance();
        $storage = $auth->getStorage()->read();
        $const = User::COL_USERNAME;
        return $storage->$const;
    }
}