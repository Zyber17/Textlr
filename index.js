var cluster = require('cluster');
if (cluster.isMaster) { //Behold the multithreding nonsense you need.
	console.log("V running in a " + (process.env.NODE_ENV ? '`' + process.env.NODE_ENV + '`' : 'production') + " environment.");

	if (process.env.NODE_ENV === 'setup') {
		// When you run this part under a setup node enviroment, it'll install the app. We're not there yet.
//		console.log('Starting setup process.');
//
//		var express = require('express');
//		var setup = require('./routes/setup');
//		var app = express();
//
//		app.configure('setup', function() {
//			return setup(function(resp) {
//				console.log(resp);
//				return process.kill(process.pid, "SIGTERM");
//			});
//		});
	} else {
		cpus = require('os').cpus().length;

		for (cpu = _i = 0; 0 <= cpus ? _i < cpus : _i > cpus; cpu = 0 <= cpus ? ++_i : --_i) { // Luna, I just love how CoffeeScript messes up even simple for loops. Ugh, I'll clean this up later.
			cluster.fork();
		}

		cluster.on('exit', function(worker) {
			console.log("Worker " + worker.id + " died :(");
			return cluster.fork();
		});
	}
} else {
	var express = require('express');
	var http = require('http');
	var app = express();
	app.configure(function() {
		app.use(express["static"](path.join(__dirname, 'public')));
		app.use(express.favicon(path.join(__dirname, 'public/images/favicon.ico')));
		app.use(express.bodyParser());
		app.set('views', __dirname + '/views');
		app.set('view engine', 'jade');
		app.disable('x-powered-by');
		app.set('port', process.env.PORT || 8000); //Because varnish will be over this and stuff, yeah
		app.use(app.router);
		app.use(function(req, res, next) {
			return res.render('errors/404', 404);
		});
		app.use(express.csrf());
	});

	app.get('/',function(req,res,next){req.end('It lives!');});
}
