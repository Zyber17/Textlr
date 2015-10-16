var cluster = require('cluster');
if (cluster.isMaster) { //Behold the multithreding nonsense you need.

	console.log("V running in a " + (process.env.NODE_ENV ? '`' + process.env.NODE_ENV + '`' : 'production') + " environment.");

	if (process.env.NODE_ENV === 'setup') {
		console.log('Starting setup process.');

		var express = require('express');
		var setup = require('./routes/setup');
		var app = express();

		app.configure('setup', function() {
			return setup(function(resp) {
				console.log(resp);
				return process.kill(process.pid, "SIGTERM");
			});
		});
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
} else if (process.env.NODE_ENV !== 'setup') {
	var express = require('express');
	var http = require('http');
	// Todo, make the actually, you know, app.
}
