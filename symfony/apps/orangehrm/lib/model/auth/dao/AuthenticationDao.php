<?php

require_once sfConfig::get('sf_root_dir').'/lib/vendor/symfony/lib/vendor/goodsalt/pbkdf2.php';

class AuthenticationDao extends BaseDao {

    /**
     *
     * @param string $username
     * @param string $password
     * @return Users 
     */
    public function getCredentials($username, $password) {
        $query = Doctrine_Query::create()
                ->from('SystemUser')
                ->where('user_name = ?', $username)
                ->andWhere('deleted = 0');
		
		$user = $query->fetchOne();
		echo $query.'<br>';
		if(validate_password($password, $user->get('user_password'))){
			return $user;
		}
		
		return null;
    }

}

