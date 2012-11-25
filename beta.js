var banner;
var i = 0;
$(document).ready(function() {
	banner = $("#betainfo");
	setTimeout('rotate();',4000);
});
function rotate() {
	var messages = ["Things are highly subject to change.",
					"The back- & front-end were partially rewritten.",
					"Bug or suggestions? Tell @Textlr on Twitter.",
					"Please, don't forget to test on mobile devices.",
					"Yep. There's now an API.",
					"All texts will be deleted at the end of the beta."];
	banner.fadeOut(500, function() {
		$(this).text(messages[i]);
		$(this).fadeIn(500, function() {
			i = ((i+1) % (messages.length));
			setTimeout('rotate();',4000);
		});
	});
}