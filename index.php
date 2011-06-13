<?php
	require 'stuff.php';
	if($_GET['key']) {
		Textlr::load($_GET['key']);
	}else{
		Textlr::main();
	}
?>