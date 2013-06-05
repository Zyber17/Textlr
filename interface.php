<?php
if($_POST['text']) { UI::post($_POST['text']); }
class UI {
	public function main() {
			ob_start("ob_gzhandler");
			//<!--<script src="/index.js"></script><script src="/textinputs.js"></script><script src="/date.js"></script><script src="/js-markdown-extra.js"></script>-->
			echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0" /><link rel="shortcut icon" href="/favicon.ico" /><title>Textlr 2.0rc1</title><link rel="stylesheet" type="text/css" href="/index.css" /><script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script><script src="/min.js"></script></head><body><div id="wrapper"><div id="typist"><header id="head"><h1>Textlr 2.0rc1</h1><h2>Simple text uploading</h2></header><form method="post" id="form" action="interface.php"><textarea id="text" name="text" placeholder="Ready to start writing? Just select here!"></textarea><input type="submit" id="submit" name="submit" value="Get a Link" /></form></div><footer><ul><li><a href="#" onclick="help();">Help</a></li><li><a href="#" onclick="commands();">Commands</a></li><li><a href="/api.html">API</a></li><li><a href="/donate.html">Donate</a></li><li><a href="https://github.com/Zyber17/Textlr">I\'m open source!</a></li></ul></footer></div><script type="text/javascript">var _gaq=_gaq||[];_gaq.push(["_setAccount","UA-29683575-1"]);_gaq.push(["_trackPageview"]);(function(){var b=document.createElement("script");b.type="text/javascript";b.async=true;b.src=("https:"==document.location.protocol?"https://ssl":"http://www")+".google-analytics.com/ga.js";var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(b,a)})();</script></body></html>';
	exit;
	}

	public function post($text) {
		require "api.php";

		$loaded = Textlr::up($text,"GXc9vHBjYMZ3KJE6M79X");

		if($loaded['errors']) {
			if($loaded['errors']['code'] == 507) {
				echo "Error";
				exit;
			}else{
				echo "Oops, something went wrong";
				exit;
			}
		} else {
			if ($loaded['response']['code'] == 200) {
				if($_POST['ajax']){
					echo json_encode($loaded['data']);
					exit;
				}else{
					header('Location: '.$loaded['data']['url']);
				}
			} else {
				echo "Oops, something went wrong";
				exit;
			}
		}
	}
	
	public function load($code) {
		require "api.php";
		$dark = '<script>function d(){var c=new Date();var a=c.getHours();var b=c.getMinutes();if((a==20&&b>=30)||(a>20)||(a<=4)){document.body.className="dark"}};</script>';

		$errorhtml = '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0" /><link rel="shortcut icon" href="/favicon.ico" /><title>Textlr: 404 Text Not Found</title><link rel="stylesheet" type="text/css" href="/page.css" />'.$dark.'</head><body onload="d();"><header id="brand"><div id="center"><a href="/" title="Textlr">Textlr</a></div></header><div id="wrapper"><article><h1 class="fourohfour">404: Oh noes! That text doesn\'t exist.<br/><a href="/" alt="Textlr">Go Home?</a></article></div><script type="text/javascript">var _gaq=_gaq||[];_gaq.push(["_setAccount","UA-29683575-1"]);_gaq.push(["_trackPageview"]);(function(){var b=document.createElement("script");b.type="text/javascript";b.async=true;b.src=("https:"==document.location.protocol?"https://ssl":"http://www")+".google-analytics.com/ga.js";var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(b,a)})();</script></body></html>';

		if(strlen($code) == 5) {
			$realcode = $code;
			$isplain = false;
		}elseif (strlen($code) > 5) {
			preg_match('/(?:(?:[\w-]+\/)|(?<!.))(\w{5})(\.txt)?(?!.)/', $code, $codematch);
			if($codematch) {
				$realcode = $codematch[1];
				if ($codematch[2] == '.txt') {
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

		$loaded = Textlr::load($realcode,"GXc9vHBjYMZ3KJE6M79X",$isplain);

		if($loaded['errors']) {
			if($loaded['errors']['code'] == 404) {
				echo $errorhtml;
				exit;
			}else{
				echo "Oops, something went wrong";
				exit;
			}
		} else {
			if ($loaded['response']['code'] == 200) {
				if($loaded['data']['title']) {
					$title = $loaded['data']['title'];
				}else{
					$title = false;
				}
				if($isplain) {
					if ($title) {
						$fname = $title;
					}else{
						$fname = $realcode;
					}
					header('Content-type: text/plain; charset= UTF-8');
					header('Content-disposition: attachment; filename='.htmlspecialchars_decode(urlencode($fname)).'.txt');
					echo($loaded['data']['text']); /*Is the same as nonplain becuase api::load returns the type based on need.*/

				}else {
					$html1 = '<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml" lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0" /><meta name="twitter:card" content="summary"><meta name="twitter:url" content="http://bs1.textlr.org/'.$realcode.'"><meta name="twitter:title" content="Textlr: '.($title ? $title : $realcode).'"><meta name="twitter:description" content="'.substr(strip_tags($loaded['data']['text']), 0, 220).'"><link rel="shortcut icon" href="/favicon.ico" /><title>Textlr'.($title ? ': '.$title : '').'</title><link rel="stylesheet" type="text/css" href="/page.css" />'.$dark.'</head><body onload="d();"><header id="brand"><div id="center"><a href="/" title="Textlr">Textlr</a></div></header><div id="wrapper"><article>';
					if ($title) {
						$title = '<h1 class="title">'.$title.'</h1>';
					}
					$html2 = '</article></div></body></html>';
					echo $html1.($title ? $title : '').$loaded['data']['text'].$html2;
					exit;
				}
		}else{
			echo "Oops, something went wrong";
			exit;
		}
	}
	}
	
}
?>