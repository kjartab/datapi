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
	
	public function getRasterDataSets() {	
		$dbresult;
		if ($this->dbconn) {
			$dbresult = @pg_query('SELECT ST_AsGeoJson(ST_Transform(ST_Envelope(rast),4236)) from dtm.norge33;');
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
		
	public function getPngDem($polygon,$table) {	
		$dbresult;
		if ($this->dbconn) {
			$dbresult = @pg_query('SELECT ST_AsGDALRaster((SELECT ST_ReClass(ST_Clip(rast,ST_Envelope(ST_Transform(ST_GeomFromText(\''.$polygon.'\',4236),32633)),true),1,\'200-1600:0-255\',\'32BF\') from dtm.norge33 WHERE ST_Intersects(rast,ST_Transform(ST_Envelope(ST_GeomFromText(\''.$polygon.'\',4236)),32633))),\'PNG\')');
			
	if ($dbresult === false) {
				return 'test';
			}
		}
			
		$row = pg_fetch_row($dbresult);
		pg_free_result($dbresult);
		if ($row === false) return;
		return pg_unescape_bytea($row[0]);
	}
	
	
	
	public function getDEM($getvars) {
		
		$outline = $getvars['outline'];
		$table = $getvars['table'];
		$format = $getvars['format'];
		$dbresult;
		if ($this->dbconn) {
				$dbresult = pg_query(
						'WITH raster as(SELECT ST_Clip(rast,ST_Envelope(ST_Transform(ST_GeomFromText(\''.$outline.'\',4236),32633)),true) ra from '.$table.' WHERE ST_Intersects(rast,ST_Transform(ST_Envelope(ST_GeomFromText(\''.$outline.'\',4236)),32633)))		
					SELECT ST_AsGDALRaster(ST_ReSample(ra,250,250,0,0,0,0,\'algorithm=Bilinear\',0.125),\''.$format.'\') from raster');
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
	
	
	
	
	public function getRasterRaw($getvars) {
		
		$outline = $getvars['outline'];
		$table = $getvars['table'];
		$format = $getvars['format'];
		$dbresult;
		if ($this->dbconn) {
				$dbresult = pg_query(
						'WITH raster as(SELECT ST_Clip(rast,ST_Envelope(ST_Transform(ST_GeomFromText(\''.$outline.'\',4236),32633)),true) ra from '.$table.' WHERE ST_Intersects(rast,ST_Transform(ST_Envelope(ST_GeomFromText(\''.$outline.'\',4236)),32633)))		
					SELECT ST_AsGDALRaster(ra,\''.$format.'\') from raster');
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
	
	public function getBoundsTest($getvars) {
		
		$outline = $getvars['outline'];
		$table = $getvars['table'];
		$format = $getvars['format'];
		$startE = $getvars['startE'];
		$startN = $getvars['startN'];
		$dimE = $getvars['dimE'];
		$dimN = $getvars['dimN'];
		$dbresult;
		if ($this->dbconn) {

				$dbresult = pg_query(
					'WITH raster as(SELECT ST_Clip(rast,ST_GeomFromText(\' POLYGON(('.$startE.' '.$startN.','.$startE.' '.($startN+$dimN).','.($startE+$dimE).' '.($startN+$dimN).','.($startE+$dimE).' '.$startN.','.$startE.' '.$startN.'))\',32633),true) ra from '.$table.' WHERE ST_Intersects(rast,ST_GeomFromText( \'POLYGON(('.$startE.' '.$startN.','.$startE.' '.($startN+$dimN).','.($startE+$dimE).' '.($startN+$dimN).','.($startE+$dimE).' '.$startN.','.$startE.' '.$startN.'))\',32633))) 	SELECT ST_AsGDALRaster(ra,\''.$format.'\') from raster');

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
