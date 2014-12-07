<?php


class Controller
{
    private $data;
    private $http_accept;
    private $method;
    private $request;
     
    public function __construct()
    {
        $this->data              = array();
        $this->http_accept       = (strpos($_SERVER['HTTP_ACCEPT'], 'json')) ? 'json' : 'xml';
        $this->method            = strtolower( $_SERVER['REQUEST_METHOD'] );
		
        // clean up and sanitize request
        $this->request = array();
        switch ( $this->method ) {
            case 'get':
            $this->data = $_GET;
            break;
            case 'post':
            $this->data = json_decode( file_get_contents( 'php://input' ), true );
            break;
            case 'put':
            $this->data = json_decode( file_get_contents( 'php://input' ), true );
            break;
            case 'delete':
            $this->data = json_decode( file_get_contents( 'php://input' ), true );
            break;
            default:
            die( Controller::respond( 400 ) );
            break;
        }
          
    }
 
	public static function respond($status = 200, $body = '', $content_type = 'text/html') {
		$httpCode = 'HTTP/1.1 ' . $status . ' ' . self::getHttpCode($status);
		header($httpCode);
		header('Content-type: ' . $content_type . '; charset=utf-8');
		header("Access-Control-Allow-Origin: *");
     
		if($body != '') {
			echo $body;
			exit;
		} else {
				$sign = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];
				 
				$message = '';
				switch($status)
				{
					case 400:
					$message = 'The API could not understand your request. Please check the documentation to ensure your requests are well formed.';
					break;
					case 401:
					$message = 'You must be authorized to view this page.';
					break;
					case 404:
					$message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
					break;
					case 500:
					$message = 'Internal servero error....';
					break;
					// add as many custom messaged as you have defined Http codes in getHttpCode()
				}
				// this should be a template
				$body = ' ' . $status . ' ' . self::getHttpCode($status) . ' ' . $message . ' '; 
				echo $body; 
			exit; 	
			} 
		}
	
    public static function getHttpCode($status) {
        // these could be stored better, in an .ini file and loaded via parse_ini_file()
        $codes = Array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }
     
    public function getData()
    {
        return $this->data;
    }
     
    public function getRequest()
    {
        return $this->request;
    }
     
    public function getMethod()
    {
        return $this->method;
    }
     
    public function getHttpAccept()
    {
        return $this->http_accept;
    }
     
}

?>