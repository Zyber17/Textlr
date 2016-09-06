var express = require('express');
//var path = require('path');
var router = express.Router();
var connectionString = process.env.DATABASE_URL || 'postgres://localhost:5432/textlr';

/* GET home page. */
router.get('/', function(req, res, next) {
	res.render('index', {title: "Textlr"});
});

router.post('/', function(req, res, next) {
	// Thanks http://mherman.org/blog/2015/02/12/postgresql-and-nodejs/
	var pg = require('pg');
	var secrets = require('./secrets.js');
	var crypto = require('crypto');
	var marked = require('marked');
	marked.setOptions({
	  renderer: new marked.Renderer(),
	  gfm: true,
	  tables: true,
	  breaks: false,
	  pedantic: false,
	  sanitize: true,
	  smartLists: true,
	  smartypants: true
	});

	var nakedtext = req.body.text;
	var text = nakedtext;
	var title = '';

	if(nakedtext.charAt(0) == '#' && nakedtext.charAt(1) != '#') {
		var slashn = text.indexOf('\n');
		var slashr = text.indexOf('\r');

		if(slashn > slashr) {
			var a  = slashn;
			slashn = slashr;
			slashr = a;
		}
		var start = (nakedtext.charAt(1) == ' ' ? 2 : 1)

		title = text.slice(start,slashn);

		if(slashr - slashn == 1) {
			text = text.slice(slashr+1)
		} else {
			text = text.slice(slashn+1)
		}
	}

	var markedtext = marked(text);

    var hash = crypto.createHmac('sha512', secrets.secret);
	var when = (new Date).getTime() - secrets.textlrEpoch;
    hash.update(when.toString());
    var id = hash.digest('base64').substring(0,10).replace(/\//g, "=");

	pg.connect(connectionString, function(err, client, done) {
        if(err) {
          done();
          console.log(err);
          return res.status(500).json({ success: false, data: err});
        }

		if(title != '') {
			client.query({
				text:   "INSERT INTO texts(id,title,markedtext,nakedtext) VALUES ($1, $2, $3, $4)",
				name:   "add-text-with-title",
				values: [id, title, markedtext, nakedtext]
			});
		} else {
			client.query({
				text:   "INSERT INTO texts(id,markedtext,nakedtext) VALUES ($1, $2, $3)",
				name:   "add-text-without-title",
				values: [id, markedtext, nakedtext]
			});
		}

    });

	res.writeHead(302, {
		'Location': '/'+(title != '' ? encodeURIComponent(title).replace('%20','+').substring(0,35) + '/' : '')+id
	});
	res.end();
});

router.get(/^.*(.{10}$)/i, function(req, res, next) {
	var pg = require('pg');

	var id = req.params[0];

	pg.connect(connectionString, function(err, client, done) {
        if(err) {
          done();
          console.log(err);
          return res.status(500).json({ success: false, data: err});
        }

		var query = client.query({
			text:   "SELECT title,markedtext FROM texts WHERE id = $1",
			name:   "select-text",
			values: [id]
		});

		var found = 0;
		query.on('row', function(row) {
			if (found == 0) {
				var title = (row.title != null ? row.title : "Textlr");
				var text  = row.markedtext;


				res.render('text', {text: text, title: title});
			}
			found++;
		});

		query.on('end', function(result) {
			rowCount = result.rowCount;
			if(rowCount != 1) {
				return res.status(404).send("Not Found");
			}
		});
	});
});

module.exports = router;
