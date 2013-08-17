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
	
	// perform a raw REST call on the server
	public function rawCall($path, $type = 'GET', $data = null) {
		// if the path isn't set, freak out
		if (!isset($path) || trim($path) == '' || substr($path, 0, 1) != '/') {
			throw new Exception('You must provide a path for your raw database call.');
		}
		// if the type isn't an acceptable method, freak out
		$acceptable_methods = array('GET', 'POST', 'DELETE');
		if (!in_array(strtoupper($type), $acceptable_methods)) {
			throw new Exception('The method type you provided is invalid.');
		}
		$type = strtoupper($type);
		// determine what to do/encode the data provided by the user
		if (is_array($data)) {
			$body = json_encode($data);
		} else if ($data != null) {
			$body = trim($data);
		} else if ($type == 'POST' && ($data == null || trim($data) == '')) {
			throw new Exception('You cannot make a POST request with no data.');
		}
		// start a new cURL session
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
		
		// if there was no result at all, freak out
		if ($raw_result == '') {
			$this->last_error = 'No result was given from the request, oh dear.';
			curl_close($ch);
			return false;
		}
		
		// parse headers of the response
		$ch_header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$response_headers = mb_substr($raw_result, 0, $ch_header_size);
		$response_headers_array = explode("\n", $response_headers);
		$response_body = mb_substr($raw_result, $ch_header_size);
		//$response_first_line = mb_substr($response_headers, 0, strpos($response_headers, "\n"));
		$response_first_line = $response_headers_array[0];
		preg_match('/http\/1\.1 (\d+) (.+)/i', $response_first_line, $http_status_matches);
		$response_status_code = $http_status_matches[1] * 1;
		if ($response_status_code != 200 && $response_status_code != 201) {
			$this->last_error = 'Method returned: '.$response_status_code.' '.$http_status_matches[2];
			curl_close($ch);
			return false;
		}
		curl_close($ch);
		
		// parse body of the response
		$json_result = json_decode($response_body, true);
		
		// if that worked, return it as an array
		if (is_array($json_result)) {
			return $json_result;
		} else {
			if ($type == 'POST' && ($path == '/node' || $path == '/node/')) {
				// parse out the new node ID from Location: header
				$new_id = 0;
				foreach ($response_headers_array as $header) {
					if (strpos($header, 'Location:') !== false) {
						preg_match('/\/node\/(\d+)/i', $header, $id_matches);
						$new_id = (int) $id_matches[1];
					}
				}
				return $new_id;
			} else if ($type == 'POST' && ($path == '/connection' || $path == '/connection/')) {
				// parse out the new connection ID from Location: header
				$new_id = 0;
				foreach ($response_headers_array as $header) {
					if (strpos($header, 'Location:') !== false) {
						preg_match('/\/connection\/(\d+)/i', $header, $id_matches);
						$new_id = (int) $id_matches[1];
					}
				}
				return $new_id;
			} else {
				return $response_body; // otherwise send the text along
			}
		}
	}
	
	// get all the nodes
	public function nodes() {
		$result = $this->rawCall('/nodes');
		if ($result == false) {
			return false;
		} else {
			return $result;
		}
	}
	
	// get all the connections
	public function connections() {
		$result = $this->rawCall('/connections');
		if ($result == false) {
			return false;
		} else {
			return $result;
		}
	}
	
	// get a specific node
	public function node($node_id) {
		if (!is_numeric($node_id) || intval($node_id) < 1) {
			throw new Exception('You must supply a unique node ID to retrieve.');
		}
		$node_id = (int) $node_id * 1;
		$result = $this->rawCall('/node/'.$node_id);
		if ($result == false) {
			return false;
		} else {
			return $result;
		}
	}
	
	// get a specific connection
	public function connection($conn_id) {
		if (!is_numeric($conn_id) || intval($conn_id) < 1) {
			throw new Exception('You must supply a unique connection ID to retrieve.');
		}
		$conn_id = (int) $conn_id * 1;
		$result = $this->rawCall('/connection/'.$conn_id);
		if ($result == false) {
			return false;
		} else {
			return $result;
		}
	}
	
	// get connections attached to a node
	public function nodeConnections($node_id) {
		if (!is_numeric($node_id) || intval($node_id) < 1) {
			throw new Exception('You must supply a unique node ID to retrieve connections for.');
		}
		$node_id = (int) $node_id * 1;
		$result = $this->rawCall('/node/'.$node_id.'/connections');
		if ($result == false) {
			return false;
		} else {
			return $result;
		}
	}
	
	// get nodes adjacent to a node
	public function adjacentNodes($node_id) {
		if (!is_numeric($node_id) || intval($node_id) < 1) {
			throw new Exception('You must supply a unique node ID to retrieve adjacent nodes.');
		}
		$node_id = (int) $node_id * 1;
		$result = $this->rawCall('/node/'.$node_id.'/adjacent');
		if ($result == false) {
			return false;
		} else {
			return $result;
		}
	}
	
	// get shortest path from source node to target node
	public function shortestPath($source_id, $target_id) {
		if (!is_numeric($source_id) || intval($source_id) < 1) {
			throw new Exception('You must supply a unique node ID as the source.');
		}
		if (!is_numeric($target_id) || intval($target_id) < 1) {
			throw new Exception('You must supply a unique node ID as the target.');
		}
		$result = $this->rawCall('/shortest/from/'.$source_id.'/to/'.$target_id);
		if ($result == false) {
			return false;
		} else {
			return $result;
		}
	}
	
	// get Euclidean distance from source node to target node
	public function distance($source_id, $target_id) {
		if (!is_numeric($source_id) || intval($source_id) < 1) {
			throw new Exception('You must supply a unique node ID as the source.');
		}
		if (!is_numeric($target_id) || intval($target_id) < 1) {
			throw new Exception('You must supply a unique node ID as the target.');
		}
		$result = $this->rawCall('/distance/from/'.$source_id.'/to/'.$target_id);
		if ($result == false) {
			return false;
		} else {
			return $result;
		}
	}
	
	// get the closest node to the given node
	public function closestTo($source_id) {
		if (!is_numeric($source_id) || intval($source_id) < 1) {
			throw new Exception('You must supply a unique node ID as the source.');
		}
		$result = $this->rawCall('/node/closest/'.$source_id);
		if ($result == false) {
			return false;
		} else {
			return $result;
		}
	}
	
	// get all nodes within a given radius of a node
	public function nearbyNodes($source_id, $radius) {
		if (!is_numeric($source_id) || intval($source_id) < 1) {
			throw new Exception('You must supply a unique node ID as the source.');
		}
		if (!is_numeric($radius) || $radius * 1 < 0) {
			throw new Exception('You must supply a radius to search within.');
		}
		$result = $this->rawCall('/nodes/nearby/'.$source_id.'/radius/'.$radius);
		if ($result == false) {
			return false;
		} else {
			return $result;
		}
	}
	
	// make a new node
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
			return $result;
		}
	}
	
	// make a new connection between two nodes
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
			return $result;
		}
	}
	
	// update a node
	public function updateNode($node) {
		if (!is_array($node)) {
			throw new Exception('You must supply an associative array of the updated attributes.');
		}
		$result = $this->rawCall('/node', 'POST', $node);
		if ($result == false) {
			return false;
		} else {
			return true;
		}
	}
	
	// update a connection
	public function updateConnection($conn) {
		if (!is_array($conn)) {
			throw new Exception('You must supply an associative array of the updated attributes.');
		}
		$result = $this->rawCall('/connection', 'POST', $conn);
		if ($result == false) {
			return false;
		} else {
			return true;
		}
	}
	
	// delete a node
	public function deleteNode($node_id) {
		if (!is_numeric($node_id) || intval($node_id) < 1) {
			throw new Exception('You must supply a unique node ID to delete.');
		}
		$node_id = (int) $node_id * 1;
		$result = $this->rawCall('/node/'.$node_id, 'DELETE');
		if ($result == false) {
			return false;
		} else {
			return true;
		}
	}
	
	// delete a connection
	public function deleteConnection($conn_id) {
		if (!is_numeric($conn_id) || intval($conn_id) < 1) {
			throw new Exception('You must supply a unique connection ID to delete.');
		}
		$conn_id = (int) $conn_id * 1;
		$result = $this->rawCall('/connection/'.$conn_id, 'DELETE');
		if ($result == false) {
			return false;
		} else {
			return true;
		}
	}
	
	// save the database
	public function save() {
		$result = $this->rawCall('/save');
		if ($result == false) {
			return false;
		} else {
			return true;
		}
	}
	
	// delete all nodes and connections
	public function deleteNodes() {
		$result = $this->rawCall('/nodes', 'DELETE');
		if ($result == false) {
			return false;
		} else {
			return true;
		}
	}
	
	// delete all connections
	public function deleteConnections() {
		$result = $this->rawCall('/connections', 'DELETE');
		if ($result == false) {
			return false;
		} else {
			return true;
		}
	}
	
}


?>
