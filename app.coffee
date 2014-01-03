cluster = require 'cluster'
if cluster.isMaster

	if process.env.NODE_ENV == 'setup'
		console.log 'Starting setup process.'

		express  =  require 'express'
		setup    =  require './routes/setup'
		app      =  express()

		app.configure 'setup', ->
			setup(resp) ->
				console.log resp
				process.kill process.pid, "SIGTERM"

	else
		cpus = require('os').cpus().length

		for cpu in [0...cpus]
			cluster.fork()

		cluster.on 'exit', (worker) ->
			# Replace the dead worker, we're not sentimental
			console.log "Worker #{worker.id} died :("
			cluster.fork()

else if process.env.NODE_ENV != 'setup'
	express		=  require 'express'
	http		=  require 'http'
	path		=  require 'path'

	

	app = express()


	app.configure ->
		app.use express.cookieParser('g8GJ3xBtIBv34LbFev09eCAEvOC3wt')
		app.use express.static(path.join(__dirname, 'public'))
		#app.use express.favicon('./public/images/favicon.ico')
		app.use express.bodyParser()
		app.use require('connect-assets')()

		app.set 'views', __dirname + '/views'
		app.set 'view engine', 'jade'

		app.disable 'x-powered-by'

		app.set 'port', process.env.PORT || 8000

		app.use app.router
		app.use express.csrf()

	
	app.get '/', (req, res)-> res.end('Hello World\n')


	app.listen app.get('port'), ->
		console.log "Express server listening on port " + app.get('port')
		console.log "Worker #{cluster.worker.id} running!"
else
	console.log "Uhh. This should never happen. See app.js."