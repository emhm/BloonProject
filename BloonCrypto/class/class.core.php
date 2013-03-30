<?php
/*
 * BloonCrypto
 * Habbo R63 Post-Shuffle
 * Based on the work of Burak, edited by BloonCrypto Git Community. (skype: burak.karamahmut)
 * 
 * https://github.com/BurakDev/BloonProject
 */
Class Core{

	/**
	*	$_instance
	*	@desc single instance of the core class
	*/
	
	private static $_instance = null;
	
	/**
	*	__construct()
	*	@desc constructor of the class Core
	*/
	
	private function __construct(){
	
	}
	
	/**
	*	getInstance()
	*	@desc get the Core singleton
	*	@return Core instance
	*/
	
	public static function getInstance(){
		if( self::$_instance === null )
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	*	say( $msg, $type )
	*	@version 1.1 (became static to be respectfull with POO rules)
	*	@param $msg string content of the message
	*	@param $type int type of message (should we print the current date ?)
	*	@desc display the specified message
	*/
	
	public static function say($msg="",$type=""){
		$print = "";
		if($type == 1){
			$print.= "[".date("d-m-Y")." ".date("H:i")."] ";
		}
		$msg = str_replace(chr(0), "[0]", $msg);
		$msg = str_replace(chr(1), "[1]", $msg);
		$msg = str_replace(chr(2), "[2]", $msg);
		$msg = str_replace(chr(3), "[3]", $msg);
		$msg = str_replace(chr(4), "[4]", $msg);
		$msg = str_replace(chr(5), "[5]", $msg);
		$msg = str_replace(chr(6), "[6]", $msg);
		$msg = str_replace(chr(7), "[7]", $msg);
		$msg = str_replace(chr(8), "[8]", $msg);
		$msg = str_replace(chr(9), "[9]", $msg);
		$msg = str_replace(chr(10), "[10]", $msg);
		$msg = str_replace(chr(11), "[11]", $msg);
		$msg = str_replace(chr(12), "[12]", $msg);
		$msg = str_replace(chr(13), "[13]", $msg);
		$msg = str_replace(chr(14), "[14]", $msg);
		$msg = str_replace(chr(15), "[15]", $msg);
		$print.= $msg."\n";
		print($print);
	}
	public function getuserbysocket($socket){
	  global $users;
	  $found=null;
	  foreach($users as $user){
		if($user->socket==$socket){ $found=$user; break; }
	  }
	  return $found;
	}
	public function disconnect($socket){
	  global $sockets,$users;
	  $found=null;
	  $n=count($users);
	  for($i=0;$i<$n;$i++){
		if($users[$i]->socket==$socket){ $found=$i; break; }
	  }
	  $usertemp = $this->getuserbysocket($socket);
	  self::say("[".$usertemp->countconnection ."] Connection lost from ".$usertemp->ip,1);
	  if(!is_null($found)){ array_splice($users,$found,1); }
	  $index = array_search($socket,$sockets);
	  socket_close($socket);
	  if($index>=0){ array_splice($sockets,$index,1); }
	}
	public function connect($socket){
	  global $sockets,$users,$CONFIG,$countconnection;
	  if(!isset($countconnection)){
		$countconnection = 0;
	  }
	  socket_getpeername($socket, $ip, $port);
	  if($CONFIG['ipaccess']){
		if(!in_array($ip, $CONFIG['ipaccesslist'])){
			self::say("Connection from ".$ip." but blacklisted.",1);
			socket_close($socket);
			return false;
		}
	  }
	  $user = new User();
	  $user->id = uniqid();
	  $user->socket = $socket;
	  $user->ip = $ip;
	  $user->port = $port;
	  $user->countconnection = $countconnection;
	  self::say("[".$countconnection."] Connection from ".$ip,1);
	  $countconnection++;
	  array_push($users,$user);
	  array_push($sockets,$socket);
	}
	public function Socket($address,$port){
	  $master=socket_create(AF_INET, SOCK_STREAM, SOL_TCP)     or die("socket_create() failed");
	  socket_set_option($master, SOL_SOCKET, SO_REUSEADDR, 1)  or die("socket_option() failed");
	  socket_bind($master, $address, $port)                    or die("socket_bind() failed");
	  socket_listen($master,20)                                or die("socket_listen() failed");
	  echo "Server Started : ".date('Y-m-d H:i:s')."\n";
	  echo "Master socket  : ".$master."\n";
	  echo "Listening on   : ".$address." port ".$port."\n\n";
	  return $master;
	}
	public function send($client,$msg){
	  socket_write($client,$msg,strlen($msg));
	  // self::say(">> ".$msg);
	}
	public function HexaString($hex){
		$string='';
		$hex = str_replace(" ", "", strtolower($hex));
		for ($i=0; $i < strlen($hex)-1; $i+=2)
		{
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		}
		return $string;
	}
	public function GetHeader($packet) {
		$packdata = substr($packet, 6);
		$packet = substr($packet, 4);
		$len = (strlen($packet)-2);
		$neg = "-".$len;
		if($len != 0){
			$packet = substr($packet, 0, $neg);
		}
		$decode = HabboEncoding::DecodeBit8($packet);
		$say = Array($decode,$packet,$packdata);
		return $say;
	}
	public function BufferParser($buffer){
		$tache = true;
		$packet = array();
		while($tache){
			if(strlen($buffer) > 0){
				$split = str_split($buffer);
				if(!isset($split[0]) || !isset($split[1]) || !isset($split[2]) || !isset($split[3])){
					break;
				}
				$lenth = HabboEncoding::DecodeBit24($split[0].$split[1].$split[2].$split[3])+4;
				$packet[] = substr($buffer, 0, $lenth);
				$buffer = substr($buffer, $lenth);
			}else{
				$tache = false;
			}
		}
		return $packet;
	}
	public function GetNextString($str){
		$stringArray = str_split($str);
		$stringLenth = HabboEncoding::DecodeBit8($stringArray[0].$stringArray[1])+2;
		$string = substr($str, 0, $stringLenth);
		$string = substr($string, 2);
		$reste = substr($str, $stringLenth);
		return Array($string, $reste);
	}
	public function OnStartTasks(){
		$this->LoadBans();
		$this->LoadRoles();
		$this->LoadHelpCategories();
		$this->LoadHelpTopics();
		$this->LoadSoundtracks();
		$this->LoadCataloguePages();
		$this->LoadCatalogueItems();
		$this->LoadNavigatorCategories();
		$this->LoadNavigatorPublics();
		$this->LoadRoomModels();
		$this->LoadRoomAds();
		$this->LoadBots();
		$this->LoadAchievements();
		Console::Beep();
		$this->StatsTasks();
	}
	public function LoadBans(){
		global $DB,$bans;
		Console::Write("Loading Bans...");
		$bans = $DB->mquery("SELECT * FROM bans");
		Console::WriteLine("completed!");
	}
	public function LoadRoles(){
		global $DB,$permissions;
		Console::Write("Loading Roles...");
		$permissions = Array();
		$permissions['ranks'] = $DB->mquery("SELECT * FROM permissions_ranks");
		$permissions['users'] = $DB->mquery("SELECT * FROM permissions_users");
		$permissions['vip'] = $DB->mquery("SELECT * FROM permissions_vip");
		Console::WriteLine("completed!");
	}
	public function LoadHelpCategories(){
		global $DB,$helpcategories;
		Console::Write("Loading Help Categories...");
		$helpcategories = $DB->mquery("SELECT * FROM help_subjects");
		Console::WriteLine("completed!");
	}
	public function LoadHelpTopics(){
		global $DB,$helptopics;
		Console::Write("Loading Help Topics...");
		$helptopics = $DB->mquery("SELECT * FROM help_topics");
		Console::WriteLine("completed!");
	}
	public function LoadSoundtracks(){
		global $DB,$soundtracks;
		Console::Write("Loading Soundtracks...");
		$soundtracks = $DB->mquery("SELECT * FROM soundtracks");
		Console::WriteLine("completed!");
	}
	public function LoadCataloguePages(){
		global $DB,$cataloguepages;
		Console::Write("Loading Catalogue Pages...");
		$cataloguepages = $DB->mquery("SELECT * FROM catalog_pages");
		Console::WriteLine("completed!");
	}
	public function LoadCatalogueItems(){
		global $DB,$catalogueitems;
		Console::Write("Loading Catalogue Items...");
		$catalogueitems = $DB->mquery("SELECT * FROM catalog_items");
		Console::WriteLine("completed!");
	}
	public function LoadNavigatorCategories(){
		global $DB,$navigatorcategories;
		Console::Write("Loading Navigator Categories...");
		$navigatorcategories = $DB->mquery("SELECT * FROM navigator_flatcats");
		Console::WriteLine("completed!");
	}
	public function LoadNavigatorPublics(){
		global $DB,$navigatorpublics;
		Console::Write("Loading Navigator Publics...");
		$navigatorpublics = $DB->mquery("SELECT * FROM navigator_publics");
		Console::WriteLine("completed!");
	}
	public function LoadRoomModels(){
		global $DB,$roommodels;
		Console::Write("Loading Room Models...");
		$roommodels = $DB->mquery("SELECT * FROM room_models");
		Console::WriteLine("completed!");
	}
	public function LoadRoomAds(){
		global $DB,$roomads;
		Console::Write("Loading Room Adverts...");
		$roomads = $DB->mquery("SELECT * FROM room_ads");
		Console::WriteLine("completed!");
	}
	public function LoadBots(){
		global $DB,$bots,$botsspeech,$botsresponses;
		Console::Write("Loading Bots...");
		$bots = $DB->mquery("SELECT * FROM bots");
		$botsspeech = $DB->mquery("SELECT * FROM bots_speech");
		$botsresponses = $DB->mquery("SELECT * FROM botsresponses");
		Console::WriteLine("completed!");
	}
	public function LoadAchievements(){
		global $DB,$achievements;
		Console::Write("Loading Achievements...");
		$achievements = $DB->mquery("SELECT * FROM achievements");
		Console::WriteLine("completed!");
	}
	public function StatsTasks(){
		global $users;
		$memory = $this->get_php_memory();
		Console::SetTitle('BloonCrypto I Users online : '.count($users).' I Rooms loaded : 0 I Memory : '.$memory);
	}
	public function get_php_memory(){
		$mem_usage = memory_get_usage(true); 
		
		if ($mem_usage < 1024){
			$result = $mem_usage." o"; 
		}else if ($mem_usage < 1048576){
			$result = round($mem_usage/1024,2)." Ko"; 
		}else{
			$result = round($mem_usage/1048576,2)." Mo"; 
		}
		return $result;
	}
}
?>