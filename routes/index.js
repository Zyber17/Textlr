var express = require('express');
//var path = require('path');
var router = express.Router();

/* GET home page. */
router.get('/', function(req, res, next) {
	res.render('index', {title: "Textlr"});
});

router.post('/', function(req, res, next) {
	// Thanks http://mherman.org/blog/2015/02/12/postgresql-and-nodejs/
	var pg = require('pg');
	var connectionString = process.env.DATABASE_URL || 'postgres://localhost:5432/textlr';
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
	var markedtext = marked(nakedtext);

    var hash = crypto.createHmac('sha512', secrets.secret);
	var when = (new Date).getTime() - secrets.textlrEpoch;
    hash.update(when.toString());
    var id = hash.digest('base64').substring(0,10).replace(/\//g, "=");

	console.log(id);
	console.log(nakedtext);
	console.log(markedtext);

	pg.connect(connectionString, function(err, client, done) {
        if(err) {
          done();
          console.log(err);
          return res.status(500).json({ success: false, data: err});
        }

		client.query({
			text:   "INSERT INTO texts(id,markedtext,nakedtext) VALUES ($1, $2, $3)",
			name:   "add-text",
			values: [id, nakedtext, markedtext]
		});
    });

	res.writeHead(302, {
		'Location': '/'+id
	});
	res.end();
});

module.exports = router;
