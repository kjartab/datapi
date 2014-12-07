<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
require_once('includes/api.lib.php');
require_once('includes/DatabaseHelper.php');
require_once('includes/DatabaseRestricted.php');
require_once('includes/db.php');


	$requestObj = new Controller();
	$dbHelper = new DatabaseHelper($db);
	$dbWriter = new DatabaseRestricted($db);
	$request = $requestObj->getRequest();

		
		$data = $requestObj->getData();
		switch( $requestObj->getMethod() ) {
			case 'get':
				
				$request = explode("/", $_SERVER['REQUEST_URI'] );
				
				$dbHelper->connect();
				
				$res = '';
				
				
				switch($request[3]) {
				
					case 'positions':
					
					 
						$res = $dbHelper->getLastPosition();
						echo $res;
					
					break;
					case 'line':
					
					 
						$res = $dbHelper->getLine();
						echo $res;
					
					break;
					
						
					case 'wkbgallen':

						$res = $dbHelper->getGallenWKB();
						
						echo $res;
						break;
						
					
					case 'gallen':
					
						$res = $dbHelper->getGallen();
						echo $res;
					
					break;
					
					case 'gallenutm32':
					
						$res = $dbHelper->getGallenUTM32();
						echo $res;
					
					break;
				}
				break;
				
			case 'post':
				$request = explode("/", $_SERVER['REQUEST_URI'] );
				print_r($request);
				switch($request[3]) {
					
					case 'positions':
					
						$postdata = $_POST;
						$dbWriter->connect();
						$dbWriter->insertLocationData($postdata);

					break;
					
				
				}
				
				try {
					
					
					
					Controller::respond(200);

				} catch (Exception $e) {
   					echo 'Caught exception: ',  $e->getMessage(), "\n";
					Controller::respond(200);
				}

				break;
				
			case 'put':
				break;
				
			case 'delete':
				break;
				
			default:
				Controller::respond( 405 );
				break;

		}
	
	exit;