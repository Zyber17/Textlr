<?php

if($_POST['client_key'] && $_POST['text']) {
	Textlr::up($_POST['text'],$_POST['client_key']);
}
if($_GET['client_key'] && $_GET['post_id']) {
	Textlr::load($_GET['post_id'],$_GET['client_key'],$_GET['plain']);
}
class Textlr {
	private function valid($dbinfo,$client_key,$type) {
		$con = mysql_connect($dbinfo['host'], $dbinfo['user'], $dbinfo['pass']) or die(mysql_error());
		$db = mysql_select_db($dbinfo['db'], $con) or die(mysql_error());

		$query  = "SELECT * FROM `clients` WHERE `key` = '".$client_key."'";
		$search = mysql_query($query, $con);
		$exists = mysql_num_rows($search);
		if ($exists != 1) {
			$errors = array(
				'response' => array(
					'code' => 401
				),
				'error' => array(
					'message' => "Invalid API key"
				)
			);
			echo json_encode($errors);
			exit;
		} else {
			$search = mysql_fetch_assoc($search);
			$id = $search['id'];
			if($type == "load") {
				$loads = $search['loads'];
				$loads++;
				$updatequery = "UPDATE  `clients` SET  `loads` =  $loads WHERE `id` = $id";
			} else if($type == "post") {
				$posts = $search['posts'];
				$posts++;
				$updatequery = "UPDATE  `clients` SET  `posts` =  $posts WHERE `id` = $id";
			} else {
				$calls = $search['calls'];
				$calls++;
				$updatequery = "UPDATE  `clients` SET  `calls` =  $calls WHERE `id` = $id";
			}
			mysql_query($updatequery, $con);
		}
		if($client_key != "GXc9vHBjYMZ3KJE6M79X") {
			Textlr::stats($dbinfo, 3);
		}
	}
	public function up($text,$client_key) {
		require 'db.php';
		Textlr::valid($dbinfo,$client_key,"post");

		if($text != null && strlen($text) > 2) {
			$con = mysql_connect($dbinfo['host'], $dbinfo['user'], $dbinfo['pass']) or die(mysql_error());
			$db = mysql_select_db($dbinfo['db'], $con) or die(mysql_error());
			
			$md = Textlr::markdown($text);
			
			$title = '';
			if (is_array($md)) {
				$marked = mysql_real_escape_string($md['text']);
				$title = mysql_real_escape_string($md['title']);
			}else {
				$marked = mysql_real_escape_string($md);
			}
			
			$ptext = mysql_real_escape_string($text);
			
			$ran = Textlr::generatecode($dbinfo);
			
			if($ran != 'Error') {

				$query = "INSERT INTO `uploads` (`id`, `text`, `plaintext`, `short_url`, `title`) VALUES (NULL, '$marked', '$ptext', '$ran', '$title');";
			
				mysql_query($query, $con);
				
				if ($title) {
					$slug = preg_replace('/ +/', '_', $md["title"]);
					$slug = preg_replace('/[^\w-]/', '', $slug);
					$slug = urlencode($slug);
					$location = $slug.'/'.$ran;
				}else {
					$location = $ran;
				}
				
				Textlr::stats($dbinfo, 0);
				$ajaxecho = array('url' => $location);
				if ($title) {
					$ajaxecho['title'] = $md['title'];
				}
				$response = array('response' => array('code' => 200),
					'data' => $ajaxecho
				);
				if($client_key == "GXc9vHBjYMZ3KJE6M79X"){
					return $response;
				}else{
					echo json_encode($response);
				}
				exit;
			}else{
				$errors = array(
					'response' => array(
						'code' => 507
					),
					'error' => array(
						'message' => "No available URL slug"
					)
				);
				if($client_key == "GXc9vHBjYMZ3KJE6M79X"){
					return $errors;
				}else{
					echo json_encode($errors);
				}
				exit;
			}
		}else{
			$errors = array(
				'response' => array(
					'code' => 403
				),
				'error' => array(
					'message' => "Text too short, it must be longer than two characters."
				)
			);
			if($client_key == "GXc9vHBjYMZ3KJE6M79X"){
				return $errors;
			}else{
				echo json_encode($errors);
			}
			exit;
		
		}
		
	}
	private function random() {			
		$characters = 'acefhijklrstuvwxyz123456789';
			$str = '';
			 for ($i = 0; $i < 5; $i++) {
			      $str .= $characters[rand(0, strlen($characters) - 1)];
			 }
		return $str;
	}
	private function generatecode($dbinfo) {
		$con = mysql_connect($dbinfo['host'], $dbinfo['user'], $dbinfo['pass']) or die(mysql_error());
		$db = mysql_select_db($dbinfo['db'], $con) or die(mysql_error());
		
		$exists = 1;
		$code = '';
		$i = 0;
		
		while ($exists != 0 && $i < 100) {
		
			$code = Textlr::random();
			
			$query  = 'SELECT * FROM `uploads` WHERE `short_url`="'.$code.'"';
			$search = mysql_query($query, $con);
			
			$exists = mysql_num_rows($search);
			
			$i++;
		}
		
		if($exists == 0) {
			return $code;
		}else {
			return 'Error';
		}
		
	}
	public function markdown($text) {
		require_once "markdown.php";
		preg_match('/(?<!.)(^(\#{1} ?([^#].+?) ?\#?)$)/sm', $text, $title); # Is there a Markdown H1 on the very first line? If so, let's snatch it up.
		$text = preg_replace('/(?<!.)(^(\#{1} ?([^#].+?) ?\#?)$)/sm', '', $text); # There's no use repeating the H1 twice, so let's just take it out of the text.
		$text = htmlspecialchars($text, ENT_QUOTES);
		$title = htmlspecialchars($title[3], ENT_QUOTES);
		$text = preg_replace('/&gt;/', '>', $text);
		$text = preg_replace('/(`.+?)&lt;(.+?`)/', '$1<$2', $text);
		$text = preg_replace('/&lt;((?:[\w-\.]+)@(?:(?:[\w]+\.)+)(?:[a-zA-Z]{2,4}))>/', '<$1>', $text);
		$text = preg_replace('/&lt;(https?:\/\/[a-zA-Z0-9-\.]+([a-zA-Z]{2,4})[\w\/\?\#\.]+)>/', '<$1>', $text);
		$text = Markdown($text);
		
		
		if ($title) {
			$array = array('title' => $title,
			'text' => $text);
			return $array;
		}else {
			return $text;
		}
		
	}


	public function load($code,$client_key,$isplain) {
		require_once('db.php');
		Textlr::valid($dbinfo,$client_key,"load");

		ob_start("ob_gzhandler");

		$con = mysql_connect($dbinfo['host'], $dbinfo['user'], $dbinfo['pass']) or die(mysql_error());
		$db = mysql_select_db($dbinfo['db'], $con) or die(mysql_error());
		
		$code = htmlspecialchars($code, ENT_QUOTES);
		$code = mysql_real_escape_string($code);

		
		if(strlen($code) == 5) {
			$realcode = $code;
		}else{
			$errors = array(
				'response' => array(
					'code' => 404
				),
				'error' => array(
					'message' => "Text not found"
				)
			);
			if($client_key == "GXc9vHBjYMZ3KJE6M79X"){
				return $errors;
			}else{
				echo json_encode($errors);
			}
			exit;
		}
		
		$query  = "SELECT * FROM `uploads` WHERE `short_url` = '".$realcode."'";
		$search = mysql_query($query, $con);
		
		if(mysql_num_rows($search) != 1) {
			$errors = array(
				'response' => array(
					'code' => 404
				),
				'error' => array(
					'message' => "Text not found"
				)
			);
			if($client_key == "GXc9vHBjYMZ3KJE6M79X"){
				return $errors;
			}else{
				echo json_encode($errors);
			}
			exit;
		}else{
			$answer = mysql_fetch_assoc($search);
			if($isplain == "true") {
				Textlr::stats($dbinfo, 2);
				$downloads = $answer['downloads'];
				$downloads++;
				$id = $answer['id'];
				$updatequery = "UPDATE `uploads` SET `downloads` =  $downloads WHERE `uploads`.`id` = $id";
				mysql_query($updatequery, $con);

				$response = array('response' => array(
					'code' => 200),
					'data' => array('text' => $answer['plaintext'],
						'title' => $answer['title'])
				);
				if ($answer['title']) {
					$response['data']['title'] = $answer['title'];
				}
				if($client_key == "GXc9vHBjYMZ3KJE6M79X"){
					return $response;
				}else{
					echo json_encode($response);
				}
				exit;		
			}else{
				Textlr::stats($dbinfo, 1);
				$views = $answer['views'];
				$views++;
				$id = $answer['id'];
				$updatequery = "UPDATE `uploads` SET `views` = $views WHERE `uploads`.`id` = $id";
				mysql_query($updatequery, $con);

				$response = array('response' => array(
					'code' => 200),
					'data' => array('text' => $answer['text'])
				);
				if ($answer['title']) {
					$response['data']['title'] = $answer['title'];
				}
				if($client_key == "GXc9vHBjYMZ3KJE6M79X"){
					return $response;
				}else{
					echo json_encode($response);
				}
				exit;
			}
		}
	}

	public function stats($dbinfo, $kind){
		$con = mysql_connect($dbinfo['host'], $dbinfo['user'], $dbinfo['pass']) or die(mysql_error());
		$db = mysql_select_db($dbinfo['db'], $con) or die(mysql_error());

		$date = date("d F, Y"); //"01 Janurary, 2013"

		$finddate  = "SELECT * FROM `stats` WHERE `date` = '".$date."'";
		$search = mysql_query($finddate, $con);

		if(mysql_num_rows($search) == 0) {
			$adddate = "INSERT INTO `stats` (`date`) VALUES ('$date');";
			mysql_query($adddate, $con);
			$search = mysql_query($finddate, $con);
		}

		$assoc = mysql_fetch_assoc($search);
		$id = $assoc['id'];

		$trow  = "SELECT * FROM `stats` WHERE `id` = '1'";
		$tsearch = mysql_query($trow, $con);
		$tassoc = mysql_fetch_assoc($tsearch);

		if($kind == 0) { //Upload
			$count = $assoc['uploads'] + 1;
			$updatequery = "UPDATE  `stats` SET  `uploads` =  $count WHERE `id` = $id";

			$tcount = $tassoc['uploads'] + 1;
			$tupdatequery = "UPDATE  `stats` SET  `uploads` =  $tcount WHERE `id` = 1";
		}else if($kind == 1) { //View
			$count = $assoc['views'] + 1;
			$updatequery = "UPDATE  `stats` SET  `views` =  $count WHERE `id` = $id";

			$tcount = $tassoc['views'] + 1;
			$tupdatequery = "UPDATE  `stats` SET  `views` =  $tcount WHERE `id` = 1";
		}else if($kind == 2) { //Download
			$count = $assoc['downloads'] + 1;
			$updatequery = "UPDATE  `stats` SET  `downloads` =  $count WHERE `id` = $id";

			$tcount = $tassoc['downloads'] + 1;
			$tupdatequery = "UPDATE  `stats` SET  `downloads` =  $tcount WHERE `id` = 1";
		}else if($kind == 3) { //API Call
			$count = $assoc['calls'] + 1;
			$updatequery = "UPDATE  `stats` SET  `calls` =  $count WHERE `id` = $id";

			$tcount = $tassoc['calls'] + 1;
			$tupdatequery = "UPDATE  `stats` SET  `calls` =  $tcount WHERE `id` = 1";
		}
		mysql_query($updatequery, $con);
		mysql_query($tupdatequery, $con);
	}
}

?>