$(document).ready(function() {
	//Something I found on jQuery, detects for an iPhone/iPad
	
	var text = $('#text');
	var preview;
	jQuery.extend(jQuery.browser,
	{SafariMobile : navigator.userAgent.toLowerCase().match(/iP(hone|ad)/i) }
	);
	
	
	if($.browser.SafariMobile) {
		window.scrollTo(0,1);
	}
	//Mobile safari can't handle the custom key events
	if(!$.browser.SafariMobile) {
		
		
		//On load, tell the user about JS fanciness and focus text box
		text.attr("placeholder",($("#text").attr("placeholder")+" Need help? Type: !help."));
		text.focus();
		
		
		//Ajax stuff
		$('#form').submit(function(event) {
		    event.preventDefault();
		    $('#submit', this).attr('disabled', 'disabled');
		    $('#text', this).attr('readonly', 'readonly').addClass('fade');
			var text = $('#text').val();
			$.post('stuff.php', {
				text:text,
				ajax:true
			},
			function(data) {
				if (data != 'Error') {
					var parsed = JSON.parse(data);
					var title;
					if (parsed.title) {
						var workingtitle;
						if(parsed.title.length > 107) {
							var cut;
							if(parsed.title.substring(105, 106) == " ") { //So much work to detect a trailing space character. Sheesh.
								cut = 105;
							}else {
								cut = 106;
							}
							workingtitle = parsed.title.substr(0, cut)+"…";
						}else {
							workingtitle = parsed.title;
						}
						title = "&text="+encodeURIComponent(workingtitle);
					} else {
						title = "";
					}
					$('#wrapper').append('<div id="url"><input type="text" value="http://textlr.org/'+parsed.url+'" onclick="this.focus();this.select();" readonly="readonly" /><a href="'+parsed.url+'">See the uploaded text.</a><a href="https://twitter.com/share?url=http://textlr.org/'+parsed.url+title+'&via=Textlr" target="_blank">Tweet it.</a>');
					$('#url input').focus().select();
				}else{
					alert('Error');
				}
			});
		});
	
		//Cmd-Enter
		text.bind("keydown", function(e) {
			if (e.metaKey && e.keyCode == 13) { $('#submit').click();}
			//Hold Cmd Alt for a preview
			else if (e.metaKey && e.altKey) {
				window["preview"](true);
			}
			
		});
		//If the preview is showing, and a key is released, destroy the preview and show and focus the textarea
		$(document).keyup(function(e) {
			if ($('.preview').length > 0) {
				hidepreview();
			}
		});
		
	}
	//via http://stackoverflow.com/questions/5590706/find-part-of-word-before-caret-in-textarea
	
	text.keyup(function() {
		var textbox = $(this);
		var end = textbox.getSelection().end;
		var result = /!(\S+)$/.exec(this.value.slice(0, end));
		var lastWord = result ? result[1] : null;
		var words = ["help","date","donate","commands","preview"];
		for(item in words) {
			if(words[item] == lastWord) {
				var returned = window[words[item]]();
				var text = textbox.val().replace('!'+words[item], returned);
				$(this).val(text);
			}
		}
	});
	
});

function preview(notbang) {
	if($('.preview').length == 0 && $('#url').length == 0) {
		var textpre = $('#text').val();
		if(textpre.length > 2) {
			if(!notbang) {
				textpre = textpre.replace(/!preview/,"")
			}
			$.post('stuff.php', {
				markdown:textpre,
				ajax:true
			},
			function(data) {
				$('#typist').hide();
				$('footer').hide();
				$('#wrapper').append("<article class='preview'>"+data+"</article>");
				var textpost = $('.preview').text();
				if (!notbang) {
					$('#wrapper').append('<button onclick="hidepreview()" class="okay" id="donepreview">I\'m done previewing.</button>');
				}
				$('#wrapper').append("<div class='textinfo'><h3>Characters: <b>"+(textpost.length-1)+"</b></h3><h3>Words: <b>"+textpost.split(" ").length+"</b></h3></div>"); //words can be off
			});
		}
	}
	if (!notbang) { return ''; }
}

function hidepreview() {
	if ($('.preview').length > 0) {
		$('#typist').show();
		$('footer').show();
		$('.preview').remove();
		$('.textinfo').remove();
		if($("#donepreview").length > 0) { $("#donepreview").remove();}
		text.blur();
		text.focus();
	}
}

function hidepopover() {
	$('#typist').show();
	$('footer').show();
	text.focus();
	$('.popover').remove();
}

function date() {
	var now = new Date();
	return $.format.date(now, "M/d/yy");
}

function donate() {
	window.location = 'donate.html';
	return '';
}

function commands() {
	text.blur();
	$('#typist').hide();
	$('footer').hide();
	$('#wrapper').append('<section id="commands" class="popover"><h1>Extendsorize.</h1><h2>Textlr has a lot <code>!commands</code> that will make using Textlr even more awesome.</h2><ul><li><h3><code>!help</code></h3><p>Brings up the <a href="javascript:hidepopover();help();">help</a> page.</p></li><li><h3><code>!commands</code></h3><p>Brings up this page</p></li><li><h3><code>!date</code></h3><p>Inserts the current date into the text in the format: <code>M/d/yy</code>. For example <em>October 8, 2012</em> would become <em>10/8/12</em>.</p></li><li><h3><code>!preview</code></h3><p>Pulls up the preview UI that is normally pulled up by ⌘⌥ (cmd+alt). (Useful when on a moble device.)</p></li><li><h3><code>!donate</code></h3><p>Redirects to the <a href="donate.html">donation page</a>.</p></li></ul><h3>Am I missing something? <a href="mailto:hello@zyber17.com?subject=Textlr Commands">Email me</a>.</h3><h2 class="okay">Got it?</h2><button onclick="hidepopover();" class="okay">Got it.</button></section>');
	return '';
}

function help() {
	text.blur();
	$('#typist').hide();
	$('footer').hide();
	$('#wrapper').append("<section id=\"help\" class=\"popover\"><h1>Have Some Help.</h1><h2>Textlr has a lot of cool features that you probably didn't know about. Here's how to use them.</h2><ul><li><h3>Markdown.</h3><p>All your documents will be parsed through <a href=\"http://daringfireball.net/projects/markdown/\">Markdown</a>.</p></li><li><h3>Title your work.</h3><p>If you're a markdown user, you know that a line starting with <code>#</code> will become a first-level heading. If the first line of your document is a first level heading we automatically use it as title of your document. It's a as simple as that.</p><ul><li><h3>I don't want a title!</h3><p>Don't want your work to be titled but want your document to being with a first level heading? No worries, Just make the top line of your document empty.</p></li></ul></li><li><h3>Preview.</h3><p>Want to preview your document? Just press and hold ⌘⌥ (cmd+alt) on a Mac. Theoretically it'd be Windows Key+Alt on a PC but that's never been tested.</p></li><li><h3>Go out with a bang.</h3><p>Textlr supports <a href=\"javascript:hidepopover();commands();\"><code>!commands</code></a> (said: \"bang commands\") that essentially are typing typing shortcuts. In fact you used one (<code>!help</code>) to get here! <a href=\"javascript:hidepopover();commands();\">Here's a list of them.</a></p></li><li><h3>I wish I didn't have to move my mouse so far to click that \"Upload\" button.</h3><p>You don't! Just press ⌘↩ (cmd+enter) when you want to submit on a Mac. Theoretically it'd be Windows Key+Enter on a PC but that's never been tested.</p></li></ul><h2 class=\"okay\">Got it?</h2><button onclick=\"hidepopover();\" class=\"okay\">Got it.</button></section>");
	return '';
}