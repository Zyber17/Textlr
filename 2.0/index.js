var elem = [],fadetime = 400;

function darkorlight() {
	var localtime = new Date();
	var hours = localtime.getHours();
	var minutes = localtime.getMinutes();

	if((hours == 20 && minutes >= 30) || (hours > 20) || (hours <= 4)) {
		elem.body.addClass('dark');
	}
}

$(document).ready(function() {

	elem.body    = $('body');
	elem.text    = $('#text');
	elem.typist  = $('#typist');
	elem.footer  = $('footer');
	elem.wrapper = $('#wrapper');
	elem.form    = $('#form');
	elem.submit  = $('#submit');

	darkorlight();

	//Something I found on jQuery, detects for an iPhone/iPad
	
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
		elem.text.attr("placeholder",(elem.text.attr("placeholder")+" Need help? Type: !help."));
		elem.text.focus();
		
		//Ajax stuff
		elem.form.submit(function(event) {
			event.preventDefault();
			elem.submit.attr('disabled', 'disabled');
			elem.text.attr('readonly', 'readonly').addClass('fade');
			var text = elem.text.val();
			$.post('interface.php', {
				text:text,
				ajax:true
			},
			function(data) {
				var parsed = JSON.parse(data);
				if (parsed.response.code == 200) {
					var pfinal = parsed.data;
					var title = [];
					if (pfinal.title) {
						var workingtitle = [];
						var cut = [];
						var adnurl;
						if(pfinal.title.length > 231) { /*ADN*/

							if(pfinal.title.substring(229, 230) == " ") { /*ADN*/
								cut.adn = 229;
							}else {
								cut.adn = 230;
							}

							adnurl = pfinal.url.split('/')[1];

							if(pfinal.title.substring(105, 106) == " ") { /*Twitter*/
								cut.twitter = 105;
							}else {
								cut.twitter = 106;
							}
							workingtitle.adn = pfinal.title.substr(0, cut.adn)+"…";
							workingtitle.twitter = pfinal.title.substr(0, cut.twitter)+"…";

						} else if (pfinal.title.length > 107 && pfinal.title.length <= 321) { /*Twitter*/
							workingtitle.adn = pfinal.title;

							adnurl = pfinal.url.split('/')[1];

							if(pfinal.title.substring(105, 106) == " ") { /*Twitter*/
								cut.twitter = 105;
							}else {
								cut.twitter = 106;
							}
							workingtitle.twitter = pfinal.title.substr(0, cut.twitter)+"…";

						} else {
							workingtitle.adn = pfinal.title;
							workingtitle.twitter = workingtitle.adn;
							adnurl = pfinal.url;
						}
						title.adn = encodeURIComponent(workingtitle.adn)+" ";
						title.twitter = "&text="+encodeURIComponent(workingtitle.twitter);
					} else {
						title.twitter = "";
						title.adn = "";
						adnurl = pfinal.url;
					}
					elem.body.append('<div class="dim show"><div id="clickdetect" onclick="newUpload()"></div><div class="box uploaded"><input type="text" value="http://textlr.org/'+pfinal.url+'" onclick="this.focus();this.select();" readonly="readonly" /><a href="'+pfinal.url+'">See the uploaded text.</a><div id="share"><ul><li><a href="https://twitter.com/share?url=http://textlr.org/'+pfinal.url+title.twitter+'&via=Textlr" target="_blank">Twitter</a></li><li><a href="https://alpha.app.net/intent/post?text='+title.adn+'http://textlr.org/'+adnurl+'">App.net</a></li></ul></div></div></div>');
					elem.dim       =  $('.dim');
					elem.uploaded  =  $('.uploaded');
					elem.url       =  $('.box input');
					elem.url.focus().select();
					setTimeout('elem.dim.removeClass("show")',fadetime);
				}else{
					elem.body.append('<div class="dim show"><div class="box dialogue error o1"><h3>'+parsed.error.message+'</h3><div class="options"><ul><li onclick="dismiss()">Okay</li></ul></div></div></div>');
					elem.edim = $('.dim');
					setTimeout('elem.edim.removeClass("show")',fadetime);
				}
			});
		});
	
		//Cmd-Enter
		elem.text.bind("keydown", function(e) {
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
	
	elem.text.keyup(function() {
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

function dismiss() {
	reenableUploadField();
	elem.edim.addClass('hide');
	setTimeout('elem.edim.remove()',fadetime);
}

function reenableUploadField() {
	elem.submit.removeAttr('disabled');
	elem.text.removeAttr('readonly').removeClass('fade').focus();
}

function newUpload() {
	if(elem.uploaded.is(':visible')) {
		elem.uploaded.addClass('hide');
		setTimeout('finishNewUpload()', fadetime);
	}
}

	function finishNewUpload() {
		elem.dim.append('<div class="box dialogue show o2"><h3>Would you like to upload another text?</h3><div class="options"><ul><li onclick="newUpload_no()">No</li><li onclick="newUpload_yes()">Yes</li></ul></div></div>');
		elem.dialogue = $('.dialogue');
		setTimeout('elem.dialogue.removeClass("show")',fadetime);
		elem.uploaded.hide().removeClass('hide');
	}

function newUpload_no() {
	elem.dialogue.addClass('hide');
	setTimeout('finishNewUpload_no()', fadetime);
}

	function finishNewUpload_no() {
		elem.dialogue.remove();
		elem.uploaded.show().addClass('show');
		elem.url.focus().select();
		setTimeout('elem.uploaded.removeClass("show")',fadetime);
	}

function newUpload_yes() {
	reenableUploadField();
	elem.text.val('');
	elem.dim.addClass('hide');
	setTimeout('elem.dim.remove()',fadetime);
}

function preview(notbang) {
	if($('.preview').length == 0 && $('#url').length == 0) {
		var textpre = elem.text.val();
		if(textpre.length > 2) {
			if(!notbang) {
				textpre = textpre.replace(/!preview/,"")
			}
			var data = Markdown(textpre);
			elem.typist.hide();
			elem.footer.hide();
			elem.wrapper.append("<article class='preview'>"+data+"</article>");
			var textpost = $('.preview').text();
			if (!notbang) {
				elem.wrapper.append('<button onclick="hidepreview()" class="okay" id="donepreview">I\'m done previewing.</button>');
			}
			elem.wrapper.append("<div class='textinfo'><h3>Characters: <b>"+(textpost.length-1)+"</b></h3><h3>Words: <b>"+textpost.split(" ").length+"</b></h3></div>"); //words can be off
		}
	}
	if (!notbang) { return ''; }
}

function hidepreview() {
	if ($('.preview').length > 0) {
		elem.typist.show();
		elem.footer.show();
		$('.preview').remove();
		$('.textinfo').remove();
		if($("#donepreview").length > 0) { $("#donepreview").remove();}
		elem.text.blur();
		elem.text.focus();
	}
}

function hidepopover() {
	elem.typist.show();
	elem.footer.show();
	elem.text.focus();
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
	elem.text.blur();
	elem.typist.hide();
	elem.footer.hide();
	elem.wrapper.append('<section id="commands" class="popover"><h1>Extendsorize.</h1><h2>Textlr has a lot <code>!commands</code> that will make using Textlr even more awesome.</h2><ul><li><h3><code>!help</code></h3><p>Brings up the <a href="javascript:hidepopover();help();">help</a> page.</p></li><li><h3><code>!commands</code></h3><p>Brings up this page</p></li><li><h3><code>!date</code></h3><p>Inserts the current date into the text in the format: <code>M/d/yy</code>. For example <em>October 8, 2012</em> would become <em>10/8/12</em>.</p></li><li><h3><code>!preview</code></h3><p>Pulls up the preview UI that is normally pulled up by ⌘⌥ (cmd+alt). (Useful when on a moble device.)</p></li><li><h3><code>!donate</code></h3><p>Redirects to the <a href="donate.html">donation page</a>.</p></li></ul><h3>Am I missing something? <a href="mailto:hello@zyber17.com?subject=Textlr Commands">Email me</a>.</h3><h2 class="okay">Got it?</h2><button onclick="hidepopover();" class="okay">Got it.</button></section>');
	return '';
}

function help() {
	elem.text.blur();
	elem.typist.hide();
	elem.footer.hide();
	elem.wrapper.append("<section id=\"help\" class=\"popover\"><h1>Have Some Help.</h1><h2>Textlr has a lot of cool features that you probably didn't know about. Here's how to use them.</h2><ul><li><h3>Markdown.</h3><p>All your documents will be pfinal.through <a href=\"http://daringfireball.net/projects/markdown/\">Markdown</a>.</p></li><li><h3>Title your work.</h3><p>If you're a markdown user, you know that a line starting with <code>#</code> will become a first-level heading. If the first line of your document is a first level heading we automatically use it as title of your document. It's a as simple as that.</p><ul><li><h3>I don't want a title!</h3><p>Don't want your work to be titled but want your document to being with a first level heading? No worries, Just make the top line of your document empty.</p></li></ul></li><li><h3>Preview.</h3><p>Want to preview your document? Just press and hold ⌘⌥ (cmd+alt) on a Mac. Theoretically it'd be Ctrl+Alt on a PC but that's never been tested.</p></li><li><h3>Go out with a bang.</h3><p>Textlr supports <a href=\"javascript:hidepopover();commands();\"><code>!commands</code></a> (said: \"bang commands\") that essentially are typing typing shortcuts. In fact you used one (<code>!help</code>) to get here! <a href=\"javascript:hidepopover();commands();\">Here's a list of them.</a></p></li><li><h3>I wish I didn't have to move my mouse so far to click that \"Upload\" button.</h3><p>You don't! Just press ⌘↩ (cmd+enter) when you want to submit on a Mac. Theoretically it'd be Ctrl+Enter on a PC but that's never been tested.</p></li></ul><h2 class=\"okay\">Got it?</h2><button onclick=\"hidepopover();\" class=\"okay\">Got it.</button></section>");
	return '';
}