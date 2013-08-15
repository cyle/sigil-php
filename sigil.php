<?php

class SIGIL {
	
	protected $sigil_host = 'localhost:8777';
	public $last_error;
	
	function __construct($sigil_host = null) {
		if (isset($sigil_host) && trim($sigil_host) != '') {
			if (filter_var('http://'.$sigil_host, FILTER_VALIDATE_URL) && strpos($sigil_host, -1) != '/') {
				if (strpos($sigil_host, ':') === false) {
					$sigil_host .= ':8777';
				}
				$this->sigil_host = $sigil_host;
			} else {
				throw new Exception('Invalid API URL given; please make sure does NOT include a trailing slash.');
			}
		}
	}
	
	public function rawCall($path, $type = 'GET', $data = null) {
		// do a raw call to the database
		if (!isset($path) || trim($path) == '' || substr($path, 0, 1) != '/') {
			throw new Exception('You must provide a path for your raw database call.');
		}
		$acceptable_methods = array('GET', 'POST', 'DELETE');
		if (!in_array(strtoupper($type), $acceptable_methods)) {
			throw new Exception('The method type you provided is invalid.');
		}
		$type = strtoupper($type);
		if (is_array($data)) {
			$body = json_encode($data);
		} else if ($data != null) {
			$body = trim($data);
		} else if ($type == 'POST' && ($data == null || trim($data) == '')) {
			throw new Exception('You cannot make a POST request with no data.');
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://'. ($this->sigil_host) . $path);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		if ($type == 'GET') {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		} else if ($type == 'POST') {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		} else if ($type == 'DELETE') {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		}
		$raw_result = curl_exec($ch);
		
		if ($raw_result == '') {
			$this->last_error = 'No result was given from the request, oh dear.';
			curl_close($ch);
			return false;
		}
		
		// parse headers of the response
		$ch_header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$response_headers = mb_substr($raw_result, 0, $ch_header_size);
		$response_body = mb_substr($raw_result, $ch_header_size);
		$response_first_line = mb_substr($response_headers, 0, strpos($response_headers, "\n"));
		preg_match('/http\/1\.1 (\d+) (.+)/i', $response_first_line, $http_status_matches);
		$response_status_code = $http_status_matches[1] * 1;
		if ($response_status_code != 200) {
			$this->last_error = 'Method returned: '.$response_status_code.' '.$http_status_matches[2];
			curl_close($ch);
			return false;
		}
		curl_close($ch);
		// parse body of the response
		$json_result = json_decode($response_body, true);
		if (is_array($json_result)) {
			return $json_result;
		} else {
			return $response_body;
		}
	}
	
	public function newNode($name = '', $x = 0, $y = 0, $z = 0, $extra = null) {
		$new_node = array();
		$new_node['Name'] = $name;
		$new_node['X'] = $x;
		$new_node['Y'] = $y;
		$new_node['Z'] = $z;
		$new_node['ExtraJSON'] = $extra;
		$result = $this->rawCall('/node', 'POST', $new_node);
		if ($result == false) {
			return false;
		} else {
			return true;
		}
	}
	
	public function newConnection($name = '', $source = null, $target = null, $extra = null) {
		$new_conn = array();
		$new_conn['Name'] = $name;
		$new_conn['Source'] = $source;
		$new_conn['Target'] = $target;
		$new_conn['ExtraJSON'] = $extra;
		$result = $this->rawCall('/connection', 'POST', $new_conn);
		if ($result == false) {
			return false;
		} else {
			return true;
		}
	}
	
}


?>
