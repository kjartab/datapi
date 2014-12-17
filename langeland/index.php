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
				
				// if data -> show overview 	
				
				if (count($request) > 3) {
			
					switch($request[3]) {
					
						// ------------- Handle all geometry queries ------------- 
						case 'spatial':
							if (count($request) == 4) {
								$res = $dbHelper->getGeometryTables();
							} else if (count($request) > 5) {
								$res = $dbHelper->getRecordFromTable($request[4],$request[5]);
							}
							
							echo $res;
							
							break;
							
						// ------------- Handle the spatiotemporal queries ------------- 
						case 'spatiotemporal':

							$res = $dbHelper->getGeoJsonTracks();
							
							echo $res;
							break;
							
							
						case 'spatiotemporalewkb':

							$res = $dbHelper->getTemporalTracksEWKB();
							
							echo $res;
							break;
							
						case 'segmentmap':

							$res = $dbHelper->getTrackSegments();
							
							echo $res;
							break;

						case 'inserted':

							$res = $dbHelper->getInsertedPoints($data['numpoints'],$data['order']);
							
							echo $res;
							break;
						case 'tracks':

							$res = $dbHelper->getTracks();
							
							echo $res;
							break;
							
						case 'trackobjects':
						
							$res = $dbHelper->getTrackObjects();
							echo $res;
							break;
							
						case 'segments':

							$res = $dbHelper->getSegmentsWKB();
							
							echo $res;
							break;
						
						case 'segmentswkbandid':

							$res = $dbHelper->getSegmentswkbandid();
							
							echo $res;
							break;
							
						case 'rawdatahours': 
							
							$table = getVariable($_GET,'table', 'rawpositiondata');
							$hours =  getVariable($_GET,'hours', 24);
							$limit = getVariable($_GET, 'limit', 100);
							$order = getVariable($_GET, 'order', 'desc');
							
							$res = $dbHelper->getRawDataHours($table, $limit, $order, $hours);
							
							echo $res;
							
							
							break;
							
						case 'rawdataoninterval':
						
							$table = getVariable($_GET,'table', 'rawpositiondata');
							$startTime =  getVariable($_GET,'starttime', '2014-12-13 00:00:00');
							$endTime =  getVariable($_GET,'endtime', '2014-12-14 00:00:00');
							$limit = getVariable($_GET, 'limit', 100);
							$order = getVariable($_GET, 'order', 'desc');
							
							$res = $dbHelper->getRawDataOnInterval($table, $limit, $order, $startTime, $endTime);
							break;
						
						break;
						
						case 'segments32632':

							$res = $dbHelper->getSegmentss32632();
							
							echo $res;
							break;	
							
							
						default:
						
								echo 'nothing	 ';
								break;
						
					
					}
					
				} else {
					header("Location: /data/langeland/data.html");
					die();
					
				} 
				
				
				if (!$res) {
					echo 'no result';
				} 
				break;
				
			case 'post':
				$postdata = $_POST["data"];
				// Ensure login credentials are correct
					
					file_put_contents('postlog.txt', $postdata);
					$dbWriter->connect();
					$dbWriter->insertTrackingPosition($postdata);

					print_r($postdata);
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
	
	
	function getVariable($list, $id, $default) {
		return empty($list[$id]) ? $default : $list[$id];
	}

	
	exit;