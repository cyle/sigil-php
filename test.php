<?php

// test out the sigil class

require('sigil.php');

$sigil = new SIGIL();


echo '<h3>making new nodes:</h3>';

$result = $sigil->newNode("node 1", 4, 4, 0, array( 'welp' => 'huh' ));
var_dump($result);
if ($result == false) {
	var_dump($sigil->last_error);
	die();
} else {
	$first_node_id = $result;
}

$result = $sigil->newNode("node 2", 6, 9, 0);
var_dump($result);
if ($result == false) {
	var_dump($sigil->last_error);
	die();
} else {
	$second_node_id = $result;
}

echo '<h3>making new connection:</h3>';

$result = $sigil->newConnection("connection 1", $first_node_id, $second_node_id);
var_dump($result);
if ($result == false) {
	var_dump($sigil->last_error);
	die();
} else {
	$first_conn_id = $result;
}

?>
