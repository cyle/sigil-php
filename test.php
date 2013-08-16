<?php

// test out the sigil class

require('sigil.php');

$sigil = new SIGIL();


echo '<h3>making new node:</h3>';

$result = $sigil->newNode("API-created node", 0, 0, 0);
var_dump($result);
if ($result == false) {
	var_dump($sigil->last_error);
	die();
}

echo '<h3>making new connection:</h3>';

?>
