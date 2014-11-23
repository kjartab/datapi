<?php
require_once('includes/api.lib.php');
require_once('includes/DatabaseHelper.php');
require_once('includes/DatabaseRestricted.php');
ini_set("memory_limit","2048M");
ini_set('max_execution_time', 300); //300 seconds = 5 minutes


	$requestObj = new Controller();
	$dbHelper = new DatabaseHelper();
	$dbWriter = new DatabaseRestricted();
	$request = $requestObj->getRequest();

		
	function default_value($list, $id, $default) {
		return empty($list[$id]) ? $default : $list[$id];
	}
		
		$data = $requestObj->getData();
		switch( $requestObj->getMethod() ) {
			case 'get':
				
				$request = explode("/", $_SERVER['REQUEST_URI'] );
				
				$dbHelper->connect();
				
				$res = '';
				
				// if data -> show overview 	
				
				
				switch($request[3]) {			
							
						case 'overview':
							
							$res = $dbHelper->getPointClouds();
							
							echo $res;
							break;
							
						case 'points':
							
							
							$res = $dbHelper->getPoints('helsinki',$_GET['outline']);
							
							echo $res;
							break;
							
						case 'polygons':
							
							$res = $dbHelper->getPolygons('helsinki',$_GET['outline']);
							
							echo $res;
							break;
						
						case 'polygonsraw':
							
							
							
							$res = $dbHelper->getPolygonsRaw('helsinki','test');
							header("Content-Type': 'application/octet-stream");
							echo $res;
							break;
						case 'test':
							
							$res = $dbHelper->gettest();
							
							echo $res;
							break;
							
				}
				
			default:
				Controller::respond( 400 );
				break;
	}
	
	exit;