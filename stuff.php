<?php
	if($_POST['text']) { Textlr::up($_POST['text']); }
	class Textlr {
		public function load($code) {
			$con = mysql_connect('localhost', 'root', 'root') or die(mysql_error());
			$db = mysql_select_db('textlr', $con) or die(mysql_error());
			
			$query  = "SELECT * FROM `uploads` WHERE `short_url` = '".$code."'";
			$search = mysql_query($query);
			$answer = mysql_fetch_assoc($search);
			$html1 = '<!DOCTYPE html>
			
			<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
				<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
					<meta name="viewport" content="width=device-width; initial-scale=1.0; user-scalable=no; maximum-scale=1.0;" />
					<title>Textlr</title>
					<style>
					body {
						width: 550px;
						background: #ffffff;
						font-family:Helvetica Neue,Helvetica,Arial,Verdana,sans-serif;
						color: #111;
						font-size: 16px;
						text-align: left;
						line-height: 1.3;
					}
					a{ color: #a90202; }
					#wrapper {
						position: absolute;
						padding-top: 40px;
						width: 550px;
						left: 50%;
						margin-left: -275px;
					}
					</style>
				</head>
				<body>
					<div id="wrapper">
					<article>';
			$html2 = '</article>
					</div>
				</body>
			</html>';
			echo $html1.$answer['text'].$html2;
		}
		public function up($text) {
			if($text != null && strlen($text) > 2) {
			$con = mysql_connect('localhost', 'root', 'root') or die(mysql_error());
			$db = mysql_select_db('textlr', $con) or die(mysql_error());
			include_once "markdown.php";
			
			$text = htmlspecialchars($text, ENT_QUOTES);
			$text =  Markdown($text);
			
			$ran = Textlr::random();
			
			$query = "INSERT INTO `uploads` (`id`, `text`, `short_url`) VALUES (NULL,'$text', '$ran');";
			mysql_query($query);
			
			if($_POST['ajax']){
				echo $ran;
			}else{
				header('Location: '.$ran);
			}
		}
			
		}
		private function random() {			
			$characters = 'acefhijklrstuvwxyz123456789';
				$str = '';
				 for ($i = 0; $i < 5; $i++) {
				      $str .= $characters[rand(0, strlen($characters) - 1)];
				 }
				if(Textlr::codeexists($str)) $str = random();
			return $str;
		}
		private function codeexists($code) {
			$con = mysql_connect('localhost', 'root', 'root') or die(mysql_error());
			$db = mysql_select_db('textlr', $con) or die(mysql_error());
			
			$query  = 'SELECT * FROM `uploads` WHERE `short_url="$code"';
			$search = mysql_query($query);
			if(mysql_num_rows($search) == 0) {
				return false;
			}else {
				return true;
			}
			
		}
		public function main() {
			echo '<!DOCTYPE html>
			
			<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
				<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
					<meta name="viewport" content="width=device-width; initial-scale=1.0; user-scalable=no; maximum-scale=1.0;" />
					<title>Textlr</title>
					<link rel="stylesheet" type="text/css" href="index.css" />
					<script src="http://code.jquery.com/jquery.min.js" type="text/javascript"></script>
					<script src="ajax.js" type="text/javascript"></script>
					<script src="showdown.js" type="text/javascript"></script>
					
				</head>
				<body>
					<div id="wrapper">
						<div id="typist">
							<header id="head">
								<h1>Textlr</h1><h2>— All your text are belong to us</h2>
							</header>
							<form method="post" id="form" action="stuff.php">
								<textarea id="text" name="text" placeholder="Ready to start writing? Just select here!"></textarea>
								<input type="submit" id="submit" name="submit" value="Upload" />
							</form>
						</div>
					</div>
				</body>
			</html>';
		}
	}
?>