<?php
	require 'interface.php';
	if($_GET['key']) {
		UI::load($_GET['key']);
	}else{
		UI::main();
	}
?>