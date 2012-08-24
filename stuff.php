<?php
	if($_POST['text']) { Textlr::up($_POST['text']); }
	if($_POST['markdown']) { Textlr::markdown($_POST['markdown'],true); }
	class Textlr {
		public function load($code) {
			require_once('db.php');
			$con = mysql_connect($dbinfo['host'], $dbinfo['user'], $dbinfo['pass']) or die(mysql_error());
			$db = mysql_select_db($dbinfo['db'], $con) or die(mysql_error());
			
			$code = htmlspecialchars($code, ENT_QUOTES);
			$code = mysql_real_escape_string($code);

			$dark = '<script>
					function darkorlight() {
    					var localtime = new Date();
    					var hours = localtime.getHours();
    					var minutes = localtime.getMinutes();

    					if((hours == 20 && minutes >= 30) || (hours > 20) || (hours <= 4)) {
    						document.body.className = "dark";
   						}
					}
					</script>';

			$errorhtml = '<!DOCTYPE html>
			
			<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
				<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
					<meta name="viewport" content="width=device-width; initial-scale=1.0; user-scalable=no; maximum-scale=1.0;" />
					<link rel="icon" type="image/png" href="/favicon.png" />
					<title>Textlr: Text Not Found</title>
					<link rel="stylesheet" type="text/css" href="/page.css" />
					'.$dark.'
				</head>
				<body onload="darkorlight();">
					<div id="wrapper">
						<article>
							<h1 class="fourohfour">404: Oh noes! That text doesn\'t exist.<br/><a href="/" alt="Textlr">Go Home?</a>
						</article>
					</div>
				</body>
			</html>';
			
			if(strlen($code) == 5) {
				$realcode = $code;
				$isplain = false;
			}elseif (strlen($code) > 5) {
				preg_match('/([\w-]+\/)?(\w{5})(\.txt)?(?!.)/', $code, $codematch);
				if($codematch) {
					$realcode = $codematch[2];
					if ($codematch[3] == '.txt') {
						$isplain = true;
					}else {
						$isplain = false;
					}
				}else {
					echo $errorhtml;
					exit;
				}
			}else{
				echo $errorhtml;
				exit;
			}
			
			$query  = "SELECT * FROM `uploads` WHERE `short_url` = '".$realcode."'";
			$search = mysql_query($query, $con);
			
			if(mysql_num_rows($search) == 0) {
				echo $errorhtml;
				exit; 
			}else{
				$answer = mysql_fetch_assoc($search);
				if($isplain) {
					if($answer['title']) {
						$fname = $answer['title'];
					}else {
						$fname = $answer['short_url'];
					}
					header('Content-type: text/plain; charset= UTF-8');
					header('Content-disposition: attachment; filename='.htmlspecialchars_decode(urlencode($fname)).'.txt');
					echo($answer['plaintext']);
				}else{
					$html1 = '<!DOCTYPE html>
					
					<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
						<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
							<meta name="viewport" content="width=device-width; initial-scale=1.0; user-scalable=no; maximum-scale=1.0;" />
							<link rel="icon" type="image/png" href="/favicon.png" />
							<title>Textlr'.($answer['title'] ? ': '.$answer['title'] : '').'</title>
							<link rel="stylesheet" type="text/css" href="/page.css" />
							'.$dark.'
					</head>
					<body onload="darkorlight();">
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
		}
		public function up($text) {
			if($text != null && strlen($text) > 2) {
			require_once('db.php');
			$con = mysql_connect($dbinfo['host'], $dbinfo['user'], $dbinfo['pass']) or die(mysql_error());
			$db = mysql_select_db($dbinfo['db'], $con) or die(mysql_error());
			
			$md = Textlr::markdown($text,false);
			
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
				
				if($_POST['ajax']){
					$ajaxecho = array("url" => $location);
					if ($title) {
						$ajaxecho['title'] = $md['title'];
					}
					echo(json_encode($ajaxecho));
				}else{
					header('Location: '.$location);
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
		public function markdown($text,$echo) {
			include_once "markdown.php";
			preg_match('/(?<!.)(^(\#{1} ?([^#].+?) ?\#?)$)/sm', $text, $title); # Is there a Markdown H1 on the very first line? If so, let's snatch it up.
			$text = preg_replace('/(?<!.)(^(\#{1} ?([^#].+?) ?\#?)$)/sm', '', $text); # There's no use repeating the H1 twice, so let's just take it out of the text.
			$text = htmlspecialchars($text, ENT_QUOTES);
			$title = htmlspecialchars($title[3], ENT_QUOTES);
			$text = preg_replace('/&gt;/', '>', $text);
			$text = Markdown($text);
			
			
			if ($echo) {
				echo ($title ? '<h1 class="title">'.$title.'</h1>' : '').$text;
			} else {
				if ($title) {
					$array = array('title' => $title,
					'text' => $text);
					return $array;
				}else {
					return $text;
				}
			}
		}
		#Lolno
		// private function darkorlight() {
		// 	$localtime = localtime(time(), true);

		// 	#if the local time is 8:30 to 8:59:59 or if the local time is 9:00 or later or if the local time is 0:00 to 4:59
		// 	if (($localtime['tm_hour'] == 20 && $localtime['tm_min'] >= 30) || ($localtime['tm_hour'] > 20) || ($localtime['tm_hour'] <= 4)) {
		// 		return true;
		// 	} else {
		// 		return false;
		// 	}
		// }
		public function main() {
			echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
	<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0; user-scalable=no; maximum-scale=1.0;" />
		<link rel="icon" type="image/png" href="/favicon.png" />
		<title>Textlr</title>
		<link rel="stylesheet" type="text/css" href="/index.css" />
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<script src="/textinputs.js"></script>
		<script src="/date.js"></script>
		<script src="/index.js"></script>

	</head>
	<body>
		<div id="wrapper">
			<div id="typist">
				<header id="head">
					<h1>Textlr</h1><h2>All your text are belong to us</h2>
				</header>
				<form method="post" id="form" action="stuff.php">
					<textarea id="text" name="text" placeholder="Ready to start writing? Just select here!"></textarea>
					<input type="submit" id="submit" name="submit" value="Get a Link" />
				</form>
			</div>
		</div>
		<footer>
			<ul>
				<li><a href="#" onclick="help();">Help</a></li>
				<li><a href="#" onclick="commands();">Commands</a></li>
				<li><a href="/donate.html">Donate</a></li>
				<li><a href="https://github.com/Zyber17/Textlr">I\'m open source!</a></li>
			</ul>
		</footer>
	</body>
</html>';
		}
	}
?>