<?php

class Database{

	private $username = 'root';
	private $password = 'root';
	private $database = 'users_administration';
	private $host = 'localhost';
	private $dsn = "mysql:host=localhost;dbname=users_administration";
	private $charset = 'utf8mb4';
	private $users_table = 'users';
	private $counter_table = 'user_counter';
	private $connection;

	private $options = [
	    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
	    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	    PDO::ATTR_EMULATE_PREPARES   => false,
	];

	public function __construct(){
		try{
		// $this->connection = new PDO($this->dsn, $this->username, $this->password, $this->options);	
		$this->connection = new PDO($this->dsn, $this->username, $this->password);	
			 // die(json_encode(array('outcome' => true)));
		}catch(PDOException $e){
			echo $e->getMessage();
			// die(json_encode(array('outcome' => false, 'message' => 'Unable to connect')));
		}		
	}

	/**
	 * Select user from the database, using an email as parameter. Returns user as AssocArray	
	 * @param string $email 
    */
	public function selectUserFromDatabase($email){
		$sql = "SELECT * FROM $this->users_table WHERE email=:email";
		$result_sql = $this->connection->prepare($sql);
		$result_sql->execute([':email' => $email]);

		return $result_sql->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Select user from the database, using firstName as parameter. Returns user as AssocArray	
	 * @param string $email 
    */
    public function selectUserByName($name){
    	$sql = "SELECT firstName, lastName, image FROM $this->users_table WHERE firstName=:name";
    	$result_sql = $this->connection->prepare($sql);
    	$result_sql->execute([':name' => $name]);

    	return $result_sql->fetch(PDO::FETCH_ASSOC);
    }

	/**
	 * Select User data from tbl-users and count likes from tbl-counter_table per user. Returns user data as AssocArray	
	 *
    */
	public function selectUsersAll($topUser=NULL){
		// Joined result from the 2 db-tables
		if($topUser === true){
			$sql = "SELECT $this->users_table.firstName, $this->users_table.image, $this->counter_table.user_id,
				(SELECT COUNT(*) FROM $this->counter_table WHERE $this->users_table.firstName = $this->counter_table.user_liked) AS likes
				FROM $this->users_table
				LEFT JOIN $this->counter_table
				ON $this->users_table.id = $this->counter_table.user_id
				ORDER BY likes DESC
				LIMIT 1";

			$result_sql = $this->connection->query($sql, PDO::FETCH_ASSOC);

			$users = [];
			foreach ($result_sql as $user) {			
				$users[] = $user;
			}

		 return $users;
		}else{
			$sql = "SELECT $this->users_table.firstName, $this->users_table.image, $this->counter_table.user_id,
				(SELECT COUNT(*) FROM $this->counter_table WHERE $this->users_table.firstName = $this->counter_table.user_liked) AS likes
				FROM $this->users_table
				LEFT JOIN $this->counter_table
				ON $this->users_table.id = $this->counter_table.user_id";

		// Return the count of the users
		// $sql = "SELECT user_liked, COUNT(*) FROM $this->counter_table GROUP BY user_liked";
			$result_sql = $this->connection->query($sql, PDO::FETCH_ASSOC);

			$users = [];
			foreach ($result_sql as $user) {			
				$users[] = $user;
			}

		 return $users;
		}
	}


	/**
	 * Check the user session in the DB	
	 * @param string $ 
	*/
	public function checkUserLogged($email, $logged){
		$sess_email = (isset($email) ? $email : '');
		$sess_logg = (isset($logged) ? $logged : '');

		$sql = "SELECT id, firstName, email, logged FROM $this->users_table WHERE email=:email AND logged=:logged";
		$result_sql = $this->connection->prepare($sql);
		$result_sql->execute([':email' => $sess_email, ':logged' => $sess_logg]);

		return $result_sql->fetchAll(PDO::FETCH_ASSOC);
	}



	/**
	 * Select user from the database, using an email as parameter. Returns user as AssocArray	
	 * @param string $reset_string 
    */
	public function selectUserResetstring($reset_string){

		$pdo_query = "SELECT * FROM $this->users_table WHERE reset_string=:reset_string";
		$pdo_request = $this->connection->prepare($pdo_query);
		$pdo_request->execute([':reset_string' => $reset_string]);

		$q_res = $pdo_request->fetch(PDO::FETCH_ASSOC);

		return $q_res;
	}


	/**
	 * Check if user exists in the database, using an email param. Return true/false
	 * @param string $email 
    */
	public function emailExist($email){
		$exist = false;
		$pdo_query = "SELECT * FROM $this->users_table WHERE email=:email";
		$pdo_request = $this->connection->prepare($pdo_query);
		$pdo_request->execute([':email' => $email]);

		$q_res = $pdo_request->fetch(PDO::FETCH_ASSOC);

		if($q_res){			
			$exist = true;
		}

		return $exist;
	}
	
	/**
	 * Insert user into the database
	 * @param string $firstName 
	 * @param string $lastName 
	 * @param string $email 
	 * @param string $password 
    */
	public function insertUserDatabase($firstName, $lastName, $email, $password){
		$pdo_query = "INSERT INTO $this->users_table (firstName, lastName, email, password, created_at, updated_at) VALUES(:firstName, :lastName, :email, :password, now(), now())"; 
	    $pdo_request = $this->connection->prepare($pdo_query);
	    $pdo_request->execute([':firstName' => $firstName, ':lastName' => $lastName, ':email' => $email, ':password' => $password]);

	    $q_res = $pdo_request->fetch(PDO::FETCH_ASSOC);	    

	   return $q_res;
	}

	/**
	 * Insert user into the database
	 * @param string $email 	 
    */
	public function deleteUserDatabase($email){
		$pdo_query = "DELETE FROM $this->users_table WHERE email=:email_p";
		$pdo_request = $this->connection->prepare($pdo_query);
		$pdo_request->execute([':email_p' => $email]);

		$q_res = $pdo_request->rowCount();

	 return $q_res;

	}

	/**
	 * Update user into the database
	 * @param string $firstName 	 
	 * @param string $lastName 	 
	 * @param string $email 	 
    */
	public function updateUserDatabase($firstName, $lastName, $updated_at, $email){

		$pdo_query = "UPDATE $this->users_table SET firstName=:firstName, lastName=:lastName, updated_at=:updated_at WHERE email=:email";
		$pdo_request = $this->connection->prepare($pdo_query);
		$pdo_request->execute([':firstName' => $firstName, ':lastName' => $lastName, ':updated_at' => $updated_at, ':email' => $email]);

		$q_res = $pdo_request->rowCount();

	 return $q_res;
	}

	/**
	 * Update user password into the database
	 * @param string $pass 	 	  
	 * @param string $id 	 	  
    */
    public function updateUserPassword($pass, $id){

    	$pdo_query = "UPDATE $this->users_table SET password=:pass WHERE id=:id";
    	$pdo_request = $this->connection->prepare($pdo_query);
    	$pdo_request->execute([':pass' => $pass, 'id' => $id]);

    	$q_res = $pdo_request->rowCount();

     return $q_res;
    }

	/**
	 * Update record into the database
	 * @param string $email 
	 * @param string $logged_param 
    */

    public function setUserLogged($email, $logged_param){

    	$pdo_query = "UPDATE $this->users_table SET logged=:logged_param WHERE email=:email_p";
    	$pdo_request = $this->connection->prepare($pdo_query);
    	$pdo_request->execute([':logged_param' => $logged_param, ':email_p' => $email]);

    	$q_res = $pdo_request->rowCount();

     return $q_res;
    }

    /**
	 * Update user reset_string field the database
	 * @param string $firstName 	 
	 * @param string $lastName 	 
	 * @param string $email 	 
    */
    public function setUserResetString($reset_string, $email){

    	$pdo_query = "UPDATE $this->users_table SET reset_string=:reset_string WHERE email=:email";
    	$pdo_request = $this->connection->prepare($pdo_query);
    	$pdo_request->execute([':reset_string' => $reset_string, ':email' => $email]);

    	$q_res = $pdo_request->rowCount();

	 return $q_res;
    }

      /**
	 * Save uploaded img to the database
	 * @param string $img 	
    */
      public function saveImg($img, $email){
      	$pdo_query = "UPDATE $this->users_table SET image=:img WHERE email=:email";
      	$pdo_request = $this->connection->prepare($pdo_query);
      	$pdo_request->execute([':img' => $img, ':email' => $email]);

      	$q_res = $pdo_request->rowCount();

      return $q_res;
      }


      /**
	 * Save the liked user name data into the database
	 * @param string $user_id, string $visiter_ip	
	 * return rows affected
    */
      public function likeUser($user_liked, $visitor_id){
      	$user_reacted = "SELECT * FROM $this->counter_table WHERE user_id=:visitor_id";
      	$user_reacted_q = $this->connection->prepare($user_reacted);
      	$user_reacted_q->execute([':visitor_id' => $visitor_id]);
      	
      	if ($user_reacted_q->rowCount() == 0 ){
      		$pdo_query_ins = "INSERT INTO $this->counter_table (user_liked, user_id, created_at) VALUES(:user_liked, :visitor_id, now() ) ";
	      	$pdo_request_ins = $this->connection->prepare($pdo_query_ins);
	      	$pdo_request_ins->execute([':user_liked' => $user_liked, ':visitor_id' => $visitor_id]);

	      	return $pdo_request_ins->rowCount();
      	}else{
      		$pdo_query_upd = "UPDATE $this->counter_table SET user_liked=:user_liked, created_at=now() WHERE user_id=:visitor_id ";
	      	$pdo_request_upd = $this->connection->prepare($pdo_query_upd);
	      	$pdo_request_upd->execute([':user_liked' => $user_liked, 'visitor_id' => $visitor_id]);

	      	return $pdo_request_upd->rowCount();
      	}      	
      }

	/**
	 * Save the liked user name data into the database
	 * @param string $user_id, string $visiter_ip	
	 * return rows affected
    */
      public function unLikeUser($user_liked, $visitor_id){
      	$pdo_query = "DELETE FROM $this->counter_table WHERE user_liked=:user_liked AND user_id=:visitor_id ";
      	$pdo_request = $this->connection->prepare($pdo_query);
      	$pdo_request->execute(['user_liked' => $user_liked, 'visitor_id' => $visitor_id]);

      	$q_res = $pdo_request->rowCount();
      	// var_dump($q_res);
      return $q_res;
	  }
	  
	/**Check the user reaction for likes
	 * @param string check_user_id
	 */
	public function checkUserReaction($logged_user_id){
		$pdo_query = "SELECT user_liked FROM $this->counter_table WHERE user_id=:logged_user_id ";
		$pdo_request = $this->connection->prepare($pdo_query);
		$pdo_request->execute([':logged_user_id' => $logged_user_id ]);

		$result = $pdo_request->fetch(PDO::FETCH_ASSOC);
		// var_dump($result);
		return $result;
	}

	/**
	 * Check if our database_schema && users_table exists into mysql information_schema
    */
	public function dataTableExist(){
		// $pdo_query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =:database_name";
		// $pdo_query = "SELECT TABLE_SCHEMA, TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA =:database_name AND TABLE_NAME=:users_table";		
		$pdo_query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA =:database_name AND TABLE_NAME=:users_table";		
		$pdo_request = $this->connection->prepare($pdo_query);
		$pdo_request->execute([':database_name' => $this->database, ':users_table' => $this->users_table]);

		$q_res = $pdo_request->rowCount();
		// var_dump($q_res); exit();
		return $q_res;
	}

	
	/**
	 * Create and initialize database table with admin inserted at creation
    */
	public function createDatabaseTable(){	
		$adminPass = password_hash('pass', PASSWORD_DEFAULT);
		$sql = "CREATE TABLE users (
		  id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		  firstName VARCHAR(40) NOT NULL,
		  lastName VARCHAR(40) NOT NULL,
		  email VARCHAR(40) NOT NULL,
		  logged VARCHAR(50),
		  password VARCHAR(99) NOT NULL,
		  reset_string VARCHAR(55),
		  image VARCHAR(100),
		  created_at VARCHAR(30),
		  updated_at VARCHAR(30)
	  );

	  INSERT INTO users (firstname, lastName, email, password, created_at, updated_at) VALUES('Administrator', 'Administrator', 'admin@gmail.com', '".$adminPass."', now(), now())";

	    $q_res = $this->connection->exec($sql);
	   	// return $q_res;
	}

}