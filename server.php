<?php
/*
 * BloonCrypto
 * Habbo R63 Post-Shuffle
 * Based on the work of Burak, edited by BloonCrypto Git Community. (skype: burak.karamahmut)
 * 
 * https://github.com/BurakDev/BloonProject/tree/BloonCrypto
 */
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

spl_autoload_register(function ($class) {
    include 'class/class.' . $class . '.php';
});

// loadClass("updater");
// loadClass("console");

Updater::Check();
// Async::call(array("Updater", "Check"), array());

Console::SetTitle("Loading BloonCrypto...");
// Async::call(array("Console", "SetTitle"), array("Loading BloonCrypto..."));

require "config.php";


Console::WriteLine("Welcome to this ALPHA 2.4 of BloonCrypto...");
// Async::call(array("Console", "WriteLine"), array("Welcome to this ALPHA 1.3 of BloonCrypto..."));
Console::WriteLine();
// Async::call(array("Console", "WriteLine"), array());
Console::WriteLine("Loading Database...completed!");
// Async::call(array("Console", "Write"), array(Loading Database..."));
Console::Write("Connecting to Bloons Database...");
// Async::call(array("Console", "Write"), array("Connecting to database..."));
try{
	if($CONFIG['mysql']['port'] != 3306){
		$portext = chr(58).$CONFIG['mysql']['port'];
	}else{
		$portext = "";
	}
	@$sql = new PDO('mysql:host='.$CONFIG['mysql']['host'].$portext.';dbname='.$CONFIG['mysql']['database'], $CONFIG['mysql']['user'], $CONFIG['mysql']['password']);
	unset($CONFIG['mysql']);
}catch(Exception $error){
	Console::WriteLine("failed!");
	// Async::call(array("Console", "WriteLine"), array("failed!"));
	Console::WriteLine("Error : ".$error->getMessage());
	// Async::call(array("Console", "WriteLine"), array("Error : ".$error->getMessage()));
	exit;
}
Console::WriteLine("completed!");
// Async::call(array("Console", "WriteLine"), array("completed!"));

Core::OnStartTasks();

$master  = Core::Socket($CONFIG['bindAddr'],$CONFIG['bindPort']);
$sockets = array($master);
$users   = array();

while(true){
  Core::StatsTasks();
  $changed = $sockets;
  $write=NULL;
  $except=NULL;
  socket_select($changed,$write,$except,NULL);
  foreach($changed as $socket){
    if($socket==$master){
      $client=socket_accept($master);
      if($client<0){ continue; }
      else{ Core::connect($client); }
    }else{
      $bytes = @socket_recv($socket,$buffer,2048,0);
      if($bytes==0){ Core::disconnect($socket); }
      else{
        $user = Core::getuserbysocket($socket);
		$packets = Core::BufferParser($buffer);
		foreach($packets as $packet){
			$packet = Core::GetHeader($packet);
			$header = $packet[0];
			$data = $packet[2];
			if($CONFIG['debug']){
				Core::say("[".$header."] ".$data,1);
			}
			$filepath = ("handler/handler_".$header.".php");
			if(file_exists($filepath)){
				@include($filepath);
			}
			unset($packet,$header,$construct,$data,$crossdomain,$ticket,$userdata,$filepath);
		}
		unset($packets,$buffer);
      }
    }
  }
}

function loadClass($class){
	include 'class/class.' . $class . '.php';
}
?>
