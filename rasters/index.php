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
	
	
	function default_value($list, $id, $default) {
		return empty($list[$id]) ? $default : $list[$id];
	}

		
		$data = $requestObj->getData();
		switch( $requestObj->getMethod() ) {
			case 'get':
				
				$request = explode("/", $_SERVER['REQUEST_URI'] );
				
				$dbHelper->connect();
				
				$res = '';
				switch($request[3]) {
					case 'rasterdata':
						
						
						$vars['srid'] = default_value($_GET,'srid','4326');
						
						$vars['outline'] = default_value($_GET,'outline','POLYGON((5.552215576171874 61.42760385286822,5.552215576171874 61.5514927834735,5.824127197265624 61.5514927834735,5.824127197265624 61.42760385286822,5.552215576171874 61.42760385286822)) ');
						
						$vars['table'] =  default_value($_GET,'dataset','vestlandet32');
						
						$vars['format'] =  default_value($_GET,'format','xyz');
						
						$res = $dbHelper->getDEM($vars);	
						
						
						switch($vars['format']) {
							case 'png':
								Controller::respond(200,$res,"Content-type: image/png");
							break;
							
							case 'xyz':
								Controller::respond(200,$res,"Content-type:'text/html");
							break;
							
							case 'raw':
								Controller::respond(200,$res,"Content-type:'application/octet-stream");
							break;
							
							case 'jpeg':
								Controller::respond(200,$res,"Content-type:'image/jpeg");
							break;
							
							default:
								
							break;
						}
						
					
					break;
					
					case 'rasterdataRaw':
						
						$vars['srid'] = default_value($_GET,'srid','4236');
						
						$vars['outline'] = default_value($_GET,'outline','POLYGON((5.552215576171874 61.42760385286822,5.552215576171874 61.5514927834735,5.824127197265624 61.5514927834735,5.824127197265624 61.42760385286822,5.552215576171874 61.42760385286822)) ');
						
						$vars['table'] =  default_value($_GET,'dataset','sunnfjordterrain');
						
						$vars['format'] =  default_value($_GET,'format','xyz');
						
						$vars['startE'] =  default_value($_GET,'startE','378618');
						$vars['startN'] =  default_value($_GET,'startN','6834147');
					
						$vars['dimE'] =  default_value($_GET,'dimE','5000');
						$vars['dimN'] =  default_value($_GET,'dimN','-5000');
						
						$res = $dbHelper->getBoundsTest($vars);	
					
						
						switch($vars['format']) {
							case 'png':
								Controller::respond(200,$res,"Content-type: image/png");
							break;
							case 'xyz':
								echo $res;
								//Controller::respond(200,$res,"Content-type:'text/html");
							break;
							
							case 'raw':
								Controller::respond(200,$res,"Content-type:'application/octet-stream");
							break;
							case 'usgdem':
								Controller::respond(200,$res,"Content-type:'text/html");
							break;
							case 'tiff':
								Controller::respond(200,$res,"Content-type:'image/jpeg");
							break;
							case 'jpeg':
								Controller::respond(200,$res,"Content-type:'image/jpeg");
							break;
							default:
							
							break;
						}
						break;
					
					case 'test':
					
						echo $dbHelper->test();
					break;
						
					
					break;
					
					case 'rasterspng':
						$res = $dbHelper->getPngDemBin($_GET['outline'],$_GET['table']);
						
						
						header("Content-type: image/png"); 
						echo $res;
					
					break;
					
					
					case 'rasters':
						
						$res = $dbHelper->getPngDem($_GET['outline'],$_GET['table']);
						header("Content-type: image/png"); 
						echo $res;
					
					break;
								
					case 'rasteroverview':
						$res = $dbHelper->getRasterDataSets();
						echo $res;
					
					break;
				}
				break;
				
			case 'post':
				
					Controller::respond(404);
				

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