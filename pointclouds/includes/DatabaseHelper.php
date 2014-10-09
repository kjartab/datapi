<?php
	
Class DatabaseHelper {
		
	private $host;
	private $port;
	private $dbname;
	private $user;
	private $password;	
	private $dbconn;
	private $dbresult;
	
	
    public function __construct() {
		$this->host = 'localhost';
		$this->port = '5432';
		$this->dbname = 'langeland';
		$this->user = 'langeland';
		$this->password = 'lillehammerol';
	}
	
	public function connect() {	
		if ($this->dbconn == null) {
			$this->dbconn = pg_connect("host=localhost port=5433 dbname=mbe user=postgres password=kjartan");
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
	

	
	public function getPointClouds() {
		$dbresult;
		if ($this->dbconn) {
			$dbresult = pg_query("SELECT * FROM pointmetadata");
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
	public function gettest() {
		$dbresult;
		if ($this->dbconn) {
			$dbresult = pg_query("select schema from pointcloud_formats where pcid=102");
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
	public function getPointsXYZ($table,$bpolygon) {
		$dbresult;
		if ($this->dbconn) {	
			
			$dbresult = pg_query("WITH patches as (SELECT array_agg(paids) paids FROM ".$table."_overlay WHERE ST_Intersects(patchgeom,ST_Transform(ST_GeomFromText('" .$bpolygon. "',4326),3067)))
							SELECT ST_X(PC_Explode(pa)::geometry),ST_Y(PC_Explode(pa)::geometry),ST_Z(PC_Explode(pa)::geometry) from ".$table." pdata, patches where pdata.id = ANY(paids) LIMIT 100;"
							);
			
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
	public function getPoints($table,$bpolygon) {
		$dbresult;
		if ($this->dbconn) {	
			
			$dbresult = pg_query("WITH points as (WITH patches as (SELECT array_agg(paids) paids FROM ".$table."_overlay WHERE ST_Intersects(patchgeom,ST_Transform(ST_GeomFromText('" .$bpolygon. "',4326),3067)))
							SELECT PC_Explode(pa) pt from ".$table." pdata, patches where pdata.id = ANY(paids))
							
							SELECT 1,ST_X(pt::geometry),ST_Y(pt::geometry),ST_Z(pt::geometry),65535,65535,65535,PC_Get(pt,'Intensity'),PC_Get(pt,'Classification')  FROM points;"
							);
							
							
			
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
	
	
	public function getPolygons($table,$bpolygon) {
		$dbresult;
		if ($this->dbconn) {	
			
			$dbresult = pg_query("WITH points as (WITH patches as (SELECT array_agg(paids) paids FROM ".$table."_overlay WHERE ST_Intersects(patchgeom,ST_Transform(ST_GeomFromText('" .$bpolygon. "',4326),3067)))
							SELECT PC_Explode(pa) pt from ".$table." pdata, patches where pdata.id = ANY(paids))
							
							SELECT ST_AsGeojson(ST_DelaunayTriangles(ST_Collect(pt::geometry)))  FROM points;"
							);
			
		if ($dbresult === false) {
				return;
			}
		}
		return $this->transformResult($dbresult);
	}
	
	
	public function getPolygonsRaw($table,$bpolygon) {
		$dbresult;
		if ($this->dbconn) {	
			
			$dbresult = pg_query("with k as (select PC_Explode(pa)::geometry pt from helsinki limit 10000) select ST_AsBinary(ST_DelaunayTriangles(ST_Collect(pt))) from k where ST_Area(pt)<2 limit 50"
							);
			
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