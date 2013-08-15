<?php

// test out the sigil class

require('sigil.php');

$sigil = new SIGIL();

$result = $sigil->rawCall('/');

var_dump($result);

if ($result == false) {
	var_dump($sigil->last_error);
}

?>
