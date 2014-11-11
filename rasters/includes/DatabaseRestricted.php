<?php
	
Class DatabaseRestricted {
		
	private $host;
	private $port;
	private $dbname;
	private $user;
	private $password;	
	private $dbconn;
	private $dbresult;
	
	
    public function __construct($db) {
		$this->dbArray = $db;
	}
	
	public function connect() {	
		if ($this->dbconn == null) {
			$this->dbconn = pg_connect($this->dbArray['connectionString']);	
		} else {
			echo 'connection already established';	
		}
	}
	
	private function transformResult($dbres) {
		$data = array();
		while ($row = pg_fetch_row($dbres)) {
			$data[] = $row;
		}
		return json_encode($data);
	}

	function handleArduinoGPS($data) {

		$resarray = array();
		$datalist = explode(',',$data);
		
		// Latitude WGS84
		$lon_deg = (double)substr($datalist[0],0,2);
		$lon_min = (double)substr($datalist[0],2,9)/60;
		$lon_deg = $lon_deg + $lon_min;
		
		// Longitude WGS84
		$lat_deg = (double)substr($datalist[2],0,3);
		$lat_min = (double)substr($datalist[2],3,9)/60;
		$lat_deg = $lat_deg + $lat_min;
		
		// Height in meters - MSL
		$height = (double)$datalist[6];
		
		$datestring = '20' .substr($datalist[4],4,2). '-' .substr($datalist[4],2,2). '-' .substr($datalist[4],0,2) . 
		' ' .substr($datalist[5],0,2). ':' .substr($datalist[5],2,2). ':' .substr($datalist[5],4,2);
		$posstring = 'ST_SetSRID(ST_MakePoint('.$lat_deg. ', ' .$lon_deg.', ' .$height. '),4326)';
		
		// Speed in knots converted to km/h
		$speed = (double)$datalist[7]*1.852; 
		
		$resarray['position'] = $posstring;
		$resarray['positiontime'] = $datestring;
		$resarray['speed'] = $speed;
				
		return $resarray;
	}

	public function handleLocationData($postdata) {

		$resarray = array();
		$extras = array();


		foreach($postdata as $key => $value) {
		
			  switch($key) {
			  	 case 'deviceName' :
					$resarray['deviceName'] = $postdata['deviceName'];
			  	 	break;

			  	 case 'x' :
					$resarray['x'] = $postdata['x'];
			  	 	break;

			  	 case 'y' :
					$resarray['y'] = $postdata['y'];
			  		break;

			  	 case 'z' :
					$resarray['z'] = $postdata['z'];
			  		 break;
					 
				case 'srid' :
					$resarray['srid'] = $postdata['srid'];
			  		 break;

			  }
		}



		print_r($postdata);
		print_r($resarray);

		return $resarray;
	}

	
	
	
	public function insertLocationData($postdata) {

		$cleandata = $this->handleLocationData($postdata);

		if ($this->dbconn) {
			pg_query('INSERT INTO positiondata(deviceName, inserttime, position) values(\''.$cleandata['deviceName']. '\', now(), ST_SetSRID(ST_MakePoint('.$cleandata['x']. ', ' .$cleandata['y'].', ' .$cleandata['z']. '),' .$cleandata['srid']. '));');
		}
		return;
	}
	
	public function insertTrackingPosition($rawdata) {
		$trackingdata = $this->handleArduinoGPS($rawdata);
		if ($this->dbconn) {
			if(!pg_query('INSERT INTO testtracking(position, positiontime, insertedtime, speed) values('.$trackingdata['position']. ', TIMESTAMP \'' .$trackingdata['positiontime']. '\', now(), ' .$trackingdata['speed']. ')'));
		}
		return;
	}
	
	public function insertIntoTable($data) {
		
		
		if ($this->dbconn) {
		
			pg_query('INSERT INTO test(the_time, theupdate) values(now(),\''.$data.'\')');
		}
		
		return;
	}
	
	
	
	
}


?>
