<?php
	
Class DatabaseHelper {
		
	private $host;
	private $port;
	private $dbname;
	private $user;
	private $password;	
	private $dbconn;
	private $dbresult;
	private $dbArray;
	
	
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

	
	public function getRasterOutline($table, $srid) {	
		$dbresult;
		if ($this->dbconn) {

				$query = 'SELECT ST_AsGeoJson(ST_Transform(ST_MakePolygon(ST_ExteriorRing(ST_Union(outline))),' .$srid. ')) from '. $table. ';';
				
			$dbresult = pg_query($query);
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}


	public function getRasterGrid($schema, $table, $srid) {
		$dbresult;
		if ($this->dbconn) {
				$query = 'SELECT ST_AsGeoJson(ST_Transform(outline,' .$srid. ')) from  '. $schema .'.'. $table. ';';

			$dbresult = pg_query($query);
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
		
		

	
	
	public function getDEM($getvars, $tableSRID, $requestSRID) {
		
		$outline = $getvars['outline'];
		$table = $getvars['table'];
		$format = $getvars['format'];
		$tableSRID = $getvars['tablesrid'];
		$requestSRID = $getvars['requestsrid'];

		$dbresult;
		if ($this->dbconn) {

				if($tableSRID != $requestSRID) {
					$query = 'SELECT rid from '.$table.' WHERE ST_Intersects(rast,ST_Transform(ST_Envelope(ST_GeomFromText(\''.$outline.'\',' .$requestSRID. ')),'. $tableSRID. ')))';
					echo $query;
					$query = 'WITH raster as(SELECT ST_Clip(rast,ST_Envelope(ST_Transform(ST_GeomFromText(\''.$outline.'\',' .$requestSRID. '),'. $tableSRID. ')),true) ra from '.$table.' WHERE ST_Intersects(rast,ST_Transform(ST_Envelope(ST_GeomFromText(\''.$outline.'\',' .$requestSRID. ')),'. $tableSRID. ')))		
					SELECT ST_AsGDALRaster(ST_ReSample(ra,250,250,0,0,0,0,\'algorithm=Bilinear\',0.125),\''.$format.'\') from raster';
					echo $query;
				}
				$dbresult = pg_query($query);
			}
		
		
		if ($dbresult === false) {
			echo 'Something didnt work out in getDEM';
			return false;
		}
		
		
		$row = pg_fetch_row($dbresult);
		pg_free_result($dbresult);
		if ($row === false) return;
		return pg_unescape_bytea($row[0]);
			
		
	}
	
	

	
}


?>
