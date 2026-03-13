<?php  

	$link = 'mysql:host=localhost;dbname=capturadora';
	$usuario = 'root';
	$pass = 'S3rvic10s.vtv';

	try{

		$pdo = new PDO($link,$usuario,$pass);

		//echo 'Conectado';
	} 
	catch (PDOException $e){
		print "¡Error!".$e->getMessage()."</br>";
		die();
	}

?>