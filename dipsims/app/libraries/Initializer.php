<?php

class Initializer {
	public static function fetch_subdomain_data($subdomain = null) {
		$d_result = array();

		$dsn = 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME');
		$user = getenv('DB_USER');
		$password = getenv('DB_PASS');

		$dbh = new \PDO($dsn, $user, $password, array(
		    \PDO::ATTR_PERSISTENT => true
		));

		if ($subdomain == 'www') {
			redirect('https://dipsims.com');
			exit;
		}
		
	    $sql = "SELECT id, db_host, db_user, db_name, db_pass, last_subscription
	    		FROM schools WHERE slug = :slug";
		$sth = $dbh->prepare($sql);
		$sth->execute(array(':slug' => $subdomain));
	    $d_result = $sth->fetch(PDO::FETCH_ASSOC);
	    $dbh = null;
    	unset($dbh);
    	 
    	
    	if (!$d_result || !count($d_result)) {
    		redirect('https://dipsims.com/subscription', 'refresh');
    		exit;
    	}
    	
		if (strtotime($d_result['last_subscription']) < strtotime('-356 days') ) {
			redirect('https://dipsims.com/subscription/expired?org=' + $subdomain, 'refresh');
			exit;
		} else
		
		return $d_result;
			
	}
	
	public static function subdomain() {
		if (getenv('ENV') == 'development') {
			return getenv('DEMO');;
		}
		$efg = null;
        preg_match('/([^.]+)\.dipsims\.com/', $_SERVER['SERVER_NAME'], $matches);
        if(isset($matches[1])) {
            $efg = $matches[1];
        }
        return $efg;
	}
}