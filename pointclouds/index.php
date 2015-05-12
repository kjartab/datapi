<?php
require_once('includes/api.lib.php');
require_once('includes/DatabaseHelper.php');
require_once('includes/DatabaseRestricted.php');
require_once('includes/db.php');
ini_set("memory_limit","2048M");
ini_set('max_execution_time', 300); //300 seconds = 5 minutes


	$requestObj = new Controller();
	$dbHelper = new DatabaseHelper();
	$dbWriter = new DatabaseRestricted();
	$request = $requestObj->getRequest();

		
		$data = $requestObj->getData();
		switch( $requestObj->getMethod() ) {
			case 'get':
				
				$request = explode("/", $_SERVER['REQUEST_URI'] );
				$dbHelper->connect($dbstring);
                
                
				$res = '';
				
				// if data -> show overview 	
				
				if (count($request) > 3 ) {
					
					switch($request[3]) {
					
						// ------------- Handle all geometry queries ------------- 
						case 'spatial':
							if (count($request)==5 OR (count($request)==6 AND $request[5] == null)) {	
	
								$res = $dbHelper->getTable($request[4]);
							} else if (count($request)==6 AND is_numeric($request[5])) {
							
								$res = $dbHelper->getRecordFromTable($request[4],$request[5]);
							} else {
								return Controller::respond(404);
							}
							
							echo $res;
							
							break;
							
							
							
                        
						case 'pointclouds':
							
							$res = $dbHelper->getPointClouds();
							
							echo $res;
							break;
							
                            
                            
                        case 'patches':
                        
                            
							$res = $dbHelper->getPatches($SCHEMA, $TABLE);
							
							echo $res;
                            break;
                            
                            
                        case 'patchesbbox':
                        
                            //echo 'test';
                            //echo $data['outline'];
                           // echo 'test';
                           $res = $dbHelper->getPatchesBbox($SCHEMA, $TABLE, $data['outline']);
							
							echo $res;
                            break;
                            
						case 'points':
							
							
							$res = $dbHelper->getPoints('helsinki',$_GET['outline']);
							
							echo $res;
							break;
                        case 'pcpoints':
                            
							$res = $dbHelper->getPcPoints('public', 'laserdata',$_GET['outline']);
							
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
				} else {
					header("Location: /cloudy-dev/data/");
					die();
					
				} 
				
				
				if (!$res) {
					echo 'error';
				} 
				break;
				
			case 'post':
				$dat = $_POST["data"];
				// Ensure login credentials are correct
					$dbWriter->connect();
					$dbWriter->insertTrackingPosition($dat)	;
					Controller::respond(200);
			
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