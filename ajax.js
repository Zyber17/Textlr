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
			//Hold Alt for a preview
			else if (e.altKey) {
				var textpre = $('#text').val()
				if(textpre.length > 2) {
					var converter = new Showdown.converter();
					var text = converter.makeHtml(textpre);
					
					$('#typist').hide();
					$('#wrapper').append("<article class='preview'>"+text+"</article>");
					$('#wrapper').append("<footer><h3>Characters: <b>"+textpre.length+"</b></h3><h3>Words: <b>"+textpre.split(" ").length+"</b></h3></footer>");
				}
			}
			
			
		});
		//If the preview is showing, and a key is released, destory the preview and show and focus the textarea
		$(document).keyup(function(e) {
			if ($('.preview')) {
				$('#typist').show();
				$('.preview').remove();
				$('footer').remove();
				$('#text').blur();
				$('#text').focus();
			}
		});
	}
	

	
});

//Add preview