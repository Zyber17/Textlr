$(document).ready(function() {
	//Something I founds on jQuery, detects for an iPhone/iPad
	var preview;
	jQuery.extend(jQuery.browser,
	{SafariMobile : navigator.userAgent.toLowerCase().match(/iP(hone|ad)/i) }
	);
	
	//Mobile safari can't handle the custom key events
	if(!$.browser.SafariMobile) {
		
		//On load, focus text box
		$('#text').focus();
		
		
		//Ajax stuff
		$('#form').submit(function(event) {
		    event.preventDefault();
		    
			var text = $('#text').val();
			$.post('stuff.php', {
				text:text,
				ajax:true
			},
			function(data) {
				window.location = data;
			});
		});
	
		//Cmd-Enter
		$("#text").bind("keydown", function(e) {
			if (e.metaKey && e.keyCode == 13) { $('#submit').click();}
			//Hold Cmd Alt for a preview
			else if (e.metaKey && e.altKey) {
				if($('.preview').length == 0) {
					var textpre = $('#text').val()
					if(textpre.length > 2) {
						var converter = new Showdown.converter();
						var text = converter.makeHtml(textpre);
						
						$('#typist').hide();
						$('#wrapper').append("<article class='preview'>"+text+"</article>");
						$('#wrapper').append("<footer><h3>Characters: <b>"+textpre.length+"</b></h3><h3>Words: <b>"+textpre.split(" ").length+"</b></h3></footer>");
					}
				}
			}
			
		});
		//If the preview is showing, and a key is released, destroy the preview and show and focus the textarea
		$(document).keyup(function(e) {
			if ($('.preview').length > 0) {
				$('#typist').show();
				$('.preview').remove();
				$('footer').remove();
				$('#text').blur();
				$('#text').focus();
			}
		});
		
	}
	//via http://stackoverflow.com/questions/5590706/find-part-of-word-before-caret-in-textarea
	
	$('#text').keyup(function() {
		var textbox = $(this);
		var end = textbox.getSelection().end;
		var result = /!(\S+)$/.exec(this.value.slice(0, end));
		var lastWord = result ? result[1] : null;
		var words = ["help","date","time"];
		for(item in words) {
			if(words[item] == lastWord) {
				window[words[item]]();
				var text = textbox.val().replace('!'+words[item], "");
				$(this).val(text);
			}
		}
	});
	
	
});

function unhelp() {
	$('#typist').show();
	$('#text').focus();
	$('#help').remove();
}

function help() {
	$('#text').blur();
	$('#typist').hide();
	$('#wrapper').append("<section id=\"help\"><h1>Have Some Help.</h1><h2>Textlr has a lot of cool features that you probably didn't know about. Here's how to use them.</h2><ul><li><h3>Markdown</h3><p>All your documents will be parsed through <a href=\"http://daringfireball.net/projects/markdown/\">Markdown</a>.</p></li><li><h3>Title Your Work</h3><p>If you're a markdown user, you know that a line starting with <code>#</code> will become a first-level heading. If the first line of your document is a first level heading we automatically use it as title of your document. It's a as simple as that.</p><ul><li><h3>I don't want a title!</h3><p>Don't want your work to be titled but want your document to being with a first level heading? No worries, Just make the top line of your document empty.</p></li></ul></li><li><h3>Preview</h3><p>Want to preview your document? Just press and hold ⌘⌥ (cmd+alt) on a Mac. Theoretically it'd be Windows Key+Alt on a PC but that's never been tested.</p></li><li><h3>I wish I didn't have to move my mouse so far to click that \"Upload\" button.</h3><p>You don't! Just press ⌘Enter (cmd+enter) when you want to submit on a Mac. Theoretically it'd be Windows Key+Enter on a PC but that's never been tested.</p></li></ul><h2 class=\"okay\">Got it?</h2><button onclick=\"unhelp();\" class=\"okay\">Got it.</button></section>");
}