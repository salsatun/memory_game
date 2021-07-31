<?php

// (B) DATABASE SETTINGS - CHANGE THESE TO YOUR OWN!
	define('DB_HOST', 'localhost');
	define('DB_NAME', 'gamemory');
	define('DB_CHARSET', 'latin1');
	define('DB_USER', 'root');
	define('DB_PASSWORD', '');

	define('VERSION', '1.0.0');
	
	define('JSON_ALL', JSON_HEX_TAG|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); 
	
	define('BadRequestException', 400);//Malformed message
	define('NotAuthorizedException', 401);//Authentication failure
	define('SessionTimedOutException', 401);//Your token is expired
	define('ForbiddenException', 403);//Not permitted to access
	define('NotFoundException', 404);//Couldn’t find resource
	define('HTTPNotAllowedException', 405);//HTTP method not supported
	define('NotAcceptableException', 406);//Client media type requested not supported
	define('NotSupportedException', 415);//Client posted media type not supported
	define('InternalServerErrorException', 500);//General server error
	define('ServiceUnavailableException', 503);//Server is temporarily unavailable or busy
	define('ConnectionTimedOutException', 1460);//Timeout. Failed Connection Attempt 
	
	@session_start();
	
	header('Access-Control-Allow-Origin: *'); 
	//header('Access-Control-Allow-Origin: http://localhost');//http://localhost
	
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, X-Authorization, Content-Type, Content-Areas');
	header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
	header('Access-Control-Allow-Credentials: true');
	
	
/* ==========================================================================
   Contenu de la Classe `Game`
   ========================================================================== */
	class Game {

	  private $pdo;
	  private $stmt;
	  public $error;
	  public $settings;
	  
	  // (A1) CONSTRUCTOR - CONNECT TO DATABASE
	  function __construct () {
		try {
			$this->pdo = new PDO(
			"mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
			DB_USER, DB_PASSWORD, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
			);

			$this->stmt = $this->pdo->prepare(
			  "SELECT * FROM `settings` WHERE 1"
			);
			$this->stmt->execute([]);
			$this->settings =  $this->stmt->fetch(PDO::FETCH_NAMED);
			if(empty($this->settings))
			{
			  $this->stmt = $this->pdo->prepare(
				"INSERT INTO `settings` () VALUES ()"
			  );
			  $this->stmt->execute([]);
			  $this->stmt = $this->pdo->prepare(
				"SELECT * FROM `settings` WHERE 1"
			  );
			  $this->stmt->execute([]);
			  $this->settings =  $this->stmt->fetch(PDO::FETCH_NAMED);
			}
		} catch (Exception $ex) {
		  
			  @header('Content-Type: application/json');
			  $response=
				[
				 'timestamp' => time(),
				 'errorCode' => ServiceUnavailableException,
				 'status' => "Connexion rejetée",
				 'error' => "Erreur de connexion à la base de données",
				 'exception' => "ServiceUnavailableException",
				 'message' => "Serveur base de données inaccessible"
				];
				$responseJson = json_encode($response,JSON_ALL);
				die($responseJson);
		}
	  }

	  // (A2) DESTRUCTOR - CLOSE DATABASE CONNECTION
	  function __destruct () {
		$this->pdo = null;
		$this->stmt = null;
	  }
  
	  // (A3) get_ip_address
	  private function get_ip_address(){
		foreach (array('HTTP_CLIENT_IP',
					   'HTTP_X_FORWARDED_FOR',
					   'HTTP_X_FORWARDED',
					   'HTTP_X_CLUSTER_CLIENT_IP',
					   'HTTP_FORWARDED_FOR',
					   'HTTP_FORWARDED',
					   'REMOTE_ADDR') as $key){
			if (array_key_exists($key, $_SERVER) === true){
				foreach (explode(',', $_SERVER[$key]) as $IPaddress){
					$IPaddress = trim($IPaddress); // Just to be safe
					//$IPaddress="8ab8:7f70::";
					//$IPaddress="197.0.0.2";
					if (filter_var($IPaddress,
								   FILTER_VALIDATE_IP,
								   FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
						!== false) 
					{
						if (filter_var($IPaddress,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6 )!== false)
							ipv6_to_ipv4($IPaddress);
						return $IPaddress;
					}
				}
			}
		}
	  }
	  
	  // (A4) get_formatted_duration
	  private function get_formatted_duration($current) {
		  $days = floor($current / (3600 * 24));
		  $hours = floor(($current - ($days * (3600 * 24)))/3600);
		  $minutes = floor(($current - ($days * (3600 * 24)) - ($hours * 3600)) / 60);
		  $seconds = floor($current - ($days * (3600 * 24)) - ($hours * 3600) - ($minutes * 60));
		  if ($days < 10) {
		  	$days = "00".$days;
		  }
		  else
		  if ($days < 100) {
		  	$days = "0".$days;
		  }
		  if ($hours < 10) {
		  	$hours = "0".$hours;
		  }
		  if ($minutes < 10) {
		  	$minutes = "0".$minutes;
		  }
		  if ($seconds < 10) {
		  	$seconds = "0".$seconds;
		  } 
		  return $minutes.":".$seconds;
	  }
	  

	  // (A5) get_duration_max
	  function get_duration_max() {
		  return $this->settings['duration_max'];
	  }

	  // (A6) get_duration_1
	  function get_duration_1() {
		  return $this->settings['duration_1'];
	  }
	  
	  // (A7) get_duration_2
	  function get_duration_2() {
		  return $this->settings['duration_2'];
	  }
	  
	  // (A8) get_duration_3
	  function get_duration_3() {
		  return $this->settings['duration_3'];
	  }
	  
	  // (A9) get_formatted_duration_max
	  function get_formatted_duration_max() {
		  return $this->get_formatted_duration($this->settings['duration_max']);
	  }

	  // (A10) get_formatted_duration_1
	  function get_formatted_duration_1() {
		  return $this->get_formatted_duration($this->settings['duration_1']);
	  }
	  
	  // (A11) get_formatted_duration_2
	  function get_formatted_duration_2() {
		  return $this->get_formatted_duration($this->settings['duration_2']);
	  }
	  
	  // (A12) get_formatted_duration_3
	  function get_formatted_duration_3() {
		  return $this->get_formatted_duration($this->settings['duration_3']);
	  }
	  
	  // (A13) update_score_duration
	  private function update_score_duration ($rank , $duration, $ip) {
		
		try {
		  $this->stmt = $this->pdo->prepare(
			"UPDATE `settings` SET `duration_$rank` = ?, `ip_$rank` = ? WHERE 1"
		  );
		  $this->stmt->execute([
			$duration, $ip
		  ]);
		  
		} catch (Exception $ex) {
		  $this->error = $ex->getMessage();
		  return false;
		}
		return true;
	  }
	  // (A14) add_new_score_duration
	  function add_new_score_duration($posted_duration) {
		  
		  @header('Content-Type: application/json');
		  if($posted_duration>=$this->settings['duration_3'])
		  {
			  $response=
				[
				 'timestamp' => time(),
				 'errorCode' => SessionTimedOutException,
				 'error' => "SessionTimedOutException",
				 'message' => "Vous avez perdu!"
				];
				$responseJson = json_encode($response,JSON_ALL);
				die($responseJson);
		  }
		  else
		  {
			  $ip=$this->get_ip_address();
			  
			  if($posted_duration<$this->settings['duration_1'])
			  {
				  $rank=1;
				  $medal="Médaille d'or";
				  $this->settings['duration_1']=$posted_duration;
				  $this->settings['ip_1']=$ip;
			  }
			  elseif($posted_duration<$this->settings['duration_2'])
			  {
				  $rank=2;
				  $medal="Médaille d'argent";
				  $this->settings['duration_2']=$posted_duration;
				  $this->settings['ip_2']=$ip;
			  }
			  else
			  {
				  $rank=3;
				  $medal="Médaille de bronze";
				  $this->settings['duration_3']=$posted_duration;
				  $this->settings['ip_3']=$ip;
			  }
			  
			  $this->update_score_duration ($rank , $posted_duration, $ip);
			  $response=
				[
				 'timestamp' => time(),
				 'ip' => $ip,
				 'rank' => $rank,
				 'duration_max' => $this->settings['duration_max'],
				 'duration_1' => $this->settings['duration_1'],
				 'duration_2' => $this->settings['duration_2'],
				 'duration_3' => $this->settings['duration_3'],
				 'formatted_duration_1' => $this->get_formatted_duration_1(),
				 'formatted_duration_2' => $this->get_formatted_duration_2(),
				 'formatted_duration_3' => $this->get_formatted_duration_3(),
				 'message' => "Vous avez gagné ($medal)!"
				];
				$responseJson = json_encode($response,JSON_ALL);
				die($responseJson);
		  }
	  }
	  
	}

// (C) CREATE NEW GAME OBJECT
$_GAME = new Game();

?>