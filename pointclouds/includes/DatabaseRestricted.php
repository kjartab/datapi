<?php
	
Class DatabaseRestricted {
		
	private $host;
	private $port;
	private $dbname;
	private $user;
	private $password;	
	private $dbconn;
	private $dbresult;
	
	
    public function __construct() {
		$this->host = 'localhost';
		$this->port = '5433';
		$this->dbname = 'langeland';
		$this->user = 'tracking';
		$this->password = 'lillehammerol';
	}
	
	public function connect() {	
		if ($this->dbconn == null) {
			$this->dbconn = pg_connect("host=localhost port=5433 dbname=langeland user=postgres password=kjartan");	
		} else {
			echo 'connection already established';	
		}
	}
	
	public function connectToDB($newdbname) {
		if ($this->dbname !=$newdbname) {
			if ($this->dbconn != null) {
				pg_close($this->dbconn);
			}
			$this->dbconn = pg_connect('host=localhost port=5433 dbname=' .$newdbname. ' user=' .$this->tracking.' password=' .$this->password.'');
			$this->dbname = $newdbname;
		}
	}
	
	public function runQuery($queryText) {
		$dbresult;
		if ($this->dbconn) {
			$dbresult = pg_query($queryText);
		}
		return $this->transformResult($dbresult);
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
	
	public function insertTrackingPosition($rawdata) {
		$trackingdata = $this->handleArduinoGPS($rawdata);
		if ($this->dbconn) {
			pg_query('INSERT INTO testtracking(position, positiontime, insertedtime, speed) values('.$trackingdata['position']. ', TIMESTAMP \'' .$trackingdata['positiontime']. '\', now(), ' .$trackingdata['speed']. ')');
		}
		return;
	}
	
	public function insertIntoTable($data) {
		
		
		if ($this->dbconn) {
		
			pg_query('INSERT INTO test(the_time, theupdate) values(now(),\''.$data.'\')');
		}
		
		return;
	}
	
	public function getTable($table) {
		$dbresult;
		if ($this->dbconn) {
			$dbresult = @pg_query('SELECT id, ST_AsGeoJson(ST_Transform(geom,4326)) FROM ' .$table. '');
			if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
	
	public function getRecordFromTable($table,$id) {
		$dbresult;
		if ($this->dbconn) {
			$dbresult = @pg_query('SELECT * FROM ' .$table.' WHERE id=' .$id. ';');
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
	// --------------- Functions specifically dealing with Langeland database tables --------------- 
	
	public function getGeoJsonTracks() {
		$dbresult;
		if ($this->dbconn) {
			$dbresult = @pg_query('SELECT row_to_json(feature_collection)
									FROM ( SELECT \'FeatureCollection\' AS type, 
										array_to_json(array_agg(feature)) As features
										FROM (	
											SELECT \'Feature\' AS type, 
											ST_AsGeoJson(ST_Transform(k.track_line,4326))::json AS geometry, 
											row_to_json((SELECT seg_id FROM (SELECT sid) AS seg_id)) AS properties
											FROM (SELECT track_line, s.id as sid FROM segment_table s) k
											) AS feature 
										) AS feature_collection;');
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
	public function getTrackSegments() {
		$dbresult;
		if ($this->dbconn) {
			$dbresult = pg_query("select array_to_json(array_agg(row_to_json(t)))
						FROM ( SELECT id, segment_id, track_id, segment_order from track_segment_table) t");
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
}


?>