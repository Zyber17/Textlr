<?php
	if($_POST['text']) { Textlr::up($_POST['text']); }
	if($_POST['markdown']) { Textlr::markdown($_POST['markdown'],true); }
	class Textlr {
		public function load($code) {
			$con = mysql_connect('localhost', 'root', 'root') or die(mysql_error());
			$db = mysql_select_db('textlr', $con) or die(mysql_error());
			
			$code = htmlspecialchars($code, ENT_QUOTES);
			$code = mysql_real_escape_string($code);
			
			$query  = "SELECT * FROM `uploads` WHERE `short_url` = '".$code."'";
			$search = mysql_query($query, $con);
			
			if(mysql_num_rows($search) == 0) {
				$errorhtml = '<!DOCTYPE html>
				
				<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
					<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
						<meta name="viewport" content="width=device-width; initial-scale=1.0; user-scalable=no; maximum-scale=1.0;" />
						<link rel="icon" type="image/png" href="favicon.png" />
						<title>Textlr: Text Not Found</title>
						<link rel="stylesheet" type="text/css" href="page.css" />
					</head>
					<body>
						<div id="wrapper">
							<article>
								<h1 class="fourohfour">404: Oh noes! That text doesn\'t exist.<br/><a href="/" alt="Textlr">Go Home?</a>
							</article>
						</div>
					</body>
				</html>';
				echo $errorhtml;
			}else{
				$answer = mysql_fetch_assoc($search);
				$html1 = '<!DOCTYPE html>
				
				<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
					<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
						<meta name="viewport" content="width=device-width; initial-scale=1.0; user-scalable=no; maximum-scale=1.0;" />
						<link rel="icon" type="image/png" href="favicon.png" />
						<title>Textlr'.($answer['title'] ? ': '.$answer['title'] : '').'</title>
						<link rel="stylesheet" type="text/css" href="page.css" />
					</head>
					<body>
						<div id="wrapper">
						<article>';
				if ($answer['title']) {
					$title = '<h1 class="title">'.$answer['title'].'</h1>';
				}
				$html2 = '</article>
						</div>
					</body>
				</html>';
				echo $html1.($answer['title'] ? $title : '').$answer['text'].$html2;
			}
		}
		public function up($text) {
			if($text != null && strlen($text) > 2) {
			$con = mysql_connect('localhost', 'root', 'root') or die(mysql_error());
			$db = mysql_select_db('textlr', $con) or die(mysql_error());
			
			$md = Textlr::markdown($text,false);
			
			$title = '';
			if (is_array($md)) {
				$text = mysql_real_escape_string($md['text']);
				$title = mysql_real_escape_string($md['title']);
			}else {
				$text = mysql_real_escape_string($md);
			}
			
			$ran = Textlr::generatecode();
			
			if($ran != 'Error') {
			
				$query = "INSERT INTO `uploads` (`id`, `text`, `short_url`, `title`) VALUES (NULL,'$text', '$ran', '$title');";
			
				mysql_query($query, $con);
				
				if($_POST['ajax']){
					echo($ran);
				}else{
					header('Location: '.$ran);
				}
			}else{
				echo('Error');
			}
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
		private function generatecode() {
			$con = mysql_connect('localhost', 'root', 'root') or die(mysql_error());
			$db = mysql_select_db('textlr', $con) or die(mysql_error());
			
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
		public function markdown($text,$echo) {
			include_once "markdown.php";
			preg_match('/(?<!.)(^(\#{1} ?([^#].+?) ?\#?)$)/sm', $text, $title); # Is there a Markdown H1 on the very first line? If so, let's snatch it up.
			$text = preg_replace('/(?<!.)(^(\#{1} ?([^#].+?) ?\#?)$)/sm', '', $text); # There's no use repeating the H1 twice, so let's just take  mamit out of the text.
			$text = htmlspecialchars($text, ENT_QUOTES);
			$text = Markdown($text);
			
			
			if ($echo) {
				echo ($title ? '<h1 class="title">'.$title[3].'</h1>' : '').$text;
			} else {
				if ($title) {
					$array = array('title' => $title[3],
					'text' => $text);
					return $array;
				}else {
					return $text;
				}
			}
		}
		public function main() {
			echo '<!DOCTYPE html>
			
			<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
				<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
					<meta name="viewport" content="width=device-width; initial-scale=1.0; user-scalable=no; maximum-scale=1.0;" />
					<link rel="icon" type="image/png" href="favicon.png" />
					<title>Textlr</title>
					<link rel="stylesheet" type="text/css" href="index.css" />
					<script src="http://code.jquery.com/jquery.min.js"></script>
					<script src="textinputs.js"></script>
					<script src="date.js"></script>
					<script src="index.js"></script>
					
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