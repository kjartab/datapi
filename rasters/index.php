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
	
	
	function getVariable($list, $id, $default) {
		return empty($list[$id]) ? $default : $list[$id];
	}

		
		$data = $requestObj->getData();

		switch( $requestObj->getMethod() ) {

			case 'get':
				
				$request = explode("/", $_SERVER['REQUEST_URI'] );
				
				$dbHelper->connect();
				
				$res = '';
				switch($request[3]) {


					case 'dtm':

						
						$vars['requestsrid'] = getVariable($_GET,'requestsrid',$REQUEST_SRID);

						$vars['outline'] = getVariable($_GET,'outline','POLYGON((5.552215576171874 61.42760385286822,5.552215576171874 61.5514927834735,5.824127197265624 61.5514927834735,5.824127197265624 61.42760385286822,5.552215576171874 61.42760385286822)) ');
						
						$vars['table'] =  getVariable($_GET,'dataset', $TABLE);
						
						$vars['schema'] = getVariable($_GET,'schema', $SCHEMA);
						
						$vars['format'] =  getVariable($_GET,'format','xyz');
						
						$vars['samplingalgorithm'] = getvariable($_GET,'samplingalgorithm', 'Bilinear');
						$res = $dbHelper->getDEM($vars);	
						
						
						switch($vars['format']) {
							case 'png':
								Controller::respond(200, $res,"Content-type: image/png");
							break;
							
							case 'xyz':
								Controller::respond(200, $res,"Content-type:'application/octet-stream");
							break;
							
							case 'raw':
								Controller::respond(200, $res,"Content-type:'application/octet-stream");
							break;
							
							case 'jpeg':
								Controller::respond(200, $res,"Content-type:'image/jpeg");
							break;
							
							default:
								
							break;
						}
						
					
					break;
					
					case 'combo':

						
						$vars['requestsrid'] = getVariable($_GET,'requestsrid',$REQUEST_SRID);

						$vars['outline'] = getVariable($_GET,'outline','POLYGON((5.552215576171874 61.42760385286822,5.552215576171874 61.5514927834735,5.824127197265624 61.5514927834735,5.824127197265624 61.42760385286822,5.552215576171874 61.42760385286822)) ');
						
						$vars['table'] =  getVariable($_GET,'dataset', $TABLE);
						
						$vars['schema'] = getVariable($_GET,'schema', $SCHEMA);
						
						$vars['format'] =  getVariable($_GET,'format','xyz');

						$vars['samplingalgorithm'] = getvariable($_GET,'samplingalgorithm', 'Bilinear');

						$res = $dbHelper->getCombinedDem($vars);
						;Controller::respond(200, $res,"Content-type:'application/octet-stream");
						break;

								
					case 'rastergrid':
						$requestSRID = getVariable($_GET,'requestsrid',$REQUEST_SRID);
						$schema = getVariable($_GET, 'schema', $SCHEMA);
						$table =  getVariable($_GET,'table', $TABLE);
						


						$res = $dbHelper->getRasterGrid($schema, $table, $requestSRID);
						echo $res;
					
					break;



								
					case 'rasteroutline':
						
						$vars['requestsrid'] = getVariable($_GET,'requestsrid',$REQUEST_SRID);
						$vars['tablesrid'] = getVariable($_GET,'tablesrid',$TABLE_SRID);
						$res = $dbHelper->getRasterOutline($vars);
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
