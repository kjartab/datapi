<?php
	
Class DatabaseHelper {
		
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
	
	public function getGeometryTables() {
		$dbresult;
		if ($this->dbconn) {
			$dbresult = @pg_query('
				SELECT table_name FROM information_schema.columns WHERE table_schema =\'public\' AND udt_name=\'geometry\' and dtd_identifier !=\'16\'');
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
			$query = 'SELECT row_to_json(feature_collection)
									FROM ( SELECT \'FeatureCollection\' AS type, 
										array_to_json(array_agg(feature)) As features
										FROM (	
											SELECT \'Feature\' AS type, 
											ST_AsGeoJson(ST_Transform(k.segment,4326))::json AS geometry, 
											row_to_json((SELECT seg_id FROM (SELECT sid, segmentorder, segmenttime, length) AS seg_id)) AS properties
											FROM (SELECT segment,segmenttime, ST_Length(segment) length, s.segmentnumber as segmentorder, s.segment_id as sid FROM prebuild_temporalsegment s) k
											) AS feature 
										) AS feature_collection;';

			$dbresult = @pg_query($query);
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
	public function getTracks() {
		$dbresult;
		if ($this->dbconn) {
			$dbresult = @pg_query('SELECT * from track_table;');
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
		
	public function getSegmentsWKB() {
		$dbresult;
		if ($this->dbconn) {
			$dbresult = @pg_query('SELECT ST_AsBinary(track_line) FROM segment_table where id=101;');
		if ($dbresult === false) {
				return;
			}
		}
		
		$row = pg_fetch_row($dbresult);
		pg_free_result($dbresult);
		if ($row === false) return;
		return pg_unescape_bytea($row[0]);
	}
	
		
		
	public function getSegmentswkbandid() {
		$dbresult;
		if ($this->dbconn) {
			$dbresult = pg_query('SELECT id, ST_AsEWKT(track_line) FROM segment_table;');
		if ($dbresult === false) {
				return;
			}
		}
		
		return $this->transformResult($dbresult);
	}
	
		
	public function getGeoJsonTracks3() {
		$dbresult;
		if ($this->dbconn) {
			$dbresult = @pg_query('SELECT row_to_json(feature) FROM (
	SELECT \'Feature\' AS type, 
	ST_AsGeoJson(ST_Transform(k.track_line,4326))::json AS geometry, 
	row_to_json((SELECT seg_id FROM (SELECT sid) AS seg_id)) AS properties
	FROM (SELECT track_line, s.id as sid FROM segment_table s) k) AS feature ;');
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}

	
	public function getRawDataHours($table, $limit, $order, $hours) {
		$dbresult;
		if ($this->dbconn) {
			if ($limit > 0) {
				$dbresult = @pg_query('SELECT id, ST_AsGeoJson(position), insertedtime, positiontime FROM ' .$table. ' WHERE insertedtime > now() - interval \'' .$hours. 'hours\' order by insertedtime '.$order.' LIMIT '.$limit.'; ');
			} else {
				$dbresult = @pg_query('SELECT id, ST_AsGeoJson(position), insertedtime, positiontime FROM ' .$table. ' WHERE insertedtime > now() - interval \'' .$hours. 'hours\' order by insertedtime '.$order.';');
			}
			
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
	
	public function getRawDataOnInterval($table, $limit, $order, $startTime, $endTime) {
		$dbresult;
		if ($this->dbconn) {
			if ($limit > 0) {
				$dbresult = @pg_query('SELECT id, ST_AsGeoJson(position), insertedtime, positiontime FROM ' .$table. ' WHERE insertedtime > TIMESTAMP \'' .$startTime. '\' AND insertedtime < TIMESTAMP \'' .$endTime. '\' order by insertedtime '.$order.' LIMIT '.$limit.'; ');
			} else {
				$dbresult = @pg_query('SELECT id, ST_AsGeoJson(position), insertedtime, positiontime FROM ' .$table. ' WHERE insertedtime > TIMESTAMP \'' .$startTime. '\' AND insertedtime < TIMESTAMP \'' .$endTime. '\' order by insertedtime '.$order.';');
			}
			
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
	
	
	public function getTrackObjects() {
		$dbresult;
		if ($this->dbconn) {
			
			$query = "SELECT row_to_json(trackdata) from (select t.id track_id, name, array_agg(segment_id) segment_ids, array_agg(segment_order) segment_order, array_agg(defines_track) defines_track
				FROM track_table t join track_segment_table ts on t.id = ts.track_id group by name, t.id) trackdata	";
			//echo $query;
//, array_agg(defines_track) defines_track
			$dbresult = pg_query($query);
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
	public function getTrackObjectsOLD() {
		$dbresult;
		if ($this->dbconn) {
			$dbresult = pg_query("SELECT row_to_json(trackdata) from (select t.id track_id, name, array_agg(segment_id) segment_ids, array_agg(segment_order) segment_order 
				FROM track_table t join track_segment_table ts on t.id = ts.track_id group by name, t.id) trackdata	");
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
	
	public function getSegments32632() {
		$dbresult;
		if ($this->dbconn) {
			$dbresult = pg_query("with ids as (select segment_id from track_segment_table ts join track_table tt on ts.track_id = tt.id where tt.name = '5km')

select ST_AsGeojson(ST_Envelope(ST_Collect(track_line))) from segment_table, ids where ids.segment_id=id;");
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
	
	public function getSegmentss32632() {
		$dbresult;
		if ($this->dbconn) {
			$dbresult = pg_query("with ids as (select segment_id from track_segment_table ts join track_table tt on ts.track_id = tt.id where tt.name = '5km')

select ST_AsGeojson(ST_Collect(track_line)) from segment_table, ids where ids.segment_id=id;");
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
	
	
	public function getTemporalTracksEWKB() {
		$dbresult;
		if ($this->dbconn) {	
			
			$dbresult = pg_query("select ST_AsBinary(ST_Collect(segment)) from prebuild_temporalsegment;");
			//$dbresult = pg_query("select ST_AsBinary(ST_Collect(ST_LineSubString(segment,0.5,0.7))) from prebuild_temporalsegment where segment_id=103;");
			
		if ($dbresult === false) {
				return;
			}
		}
		
		
		$row = pg_fetch_row($dbresult);
		pg_free_result($dbresult);
		if ($row === false) return;
		return pg_unescape_bytea($row[0]);
	}
	
	
}


?>