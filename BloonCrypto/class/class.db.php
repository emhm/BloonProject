<?php
/*
 * BloonCrypto
 * Habbo R63 Post-Shuffle
 * Based on the work of Burak, edited by BloonCrypto Git Community. (skype: burak.karamahmut)
 * 
 * https://github.com/BurakDev/BloonProject
 */
Class DB{
	public function query($req){
		global $sql;
		$exe = $sql->prepare($req);
		$exe->execute();
		if($exe->rowCount() < 1){
			return false;
		}else if($exe->rowCount() == 1){
			return $exe->fetch(PDO::FETCH_OBJ);
		}
	}
	public function mquery($req){
		global $sql;
		$exe = $sql->prepare($req);
		$exe->execute();
		if($exe->rowCount() < 1){
			return false;
		}else{
			$result = array();
			while($query = $exe->fetch(PDO::FETCH_OBJ)){
				$result[] = $query;
			}
			return $result;
		}
	}
	public function numrows($req){
		global $sql;
		$exe = $sql->prepare($req);
		$exe->execute();
		return $exe->rowCount();
	}
	public function exec($req){
		global $sql;
		$exe = $sql->prepare($req);
		$exe->execute();
		return true;
	}
}
?>