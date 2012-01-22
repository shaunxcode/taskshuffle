express = require "express"
app = express.createServer()
io = require('socket.io').listen(app)
ts = require "./taskshuffle"
port = 1337

staticDir = (dir) -> app.use "/" + dir, express.static __dirname + "/../public/" + dir

app.configure -> 
	app.use express.bodyParser()
	staticDir "chosen"
	staticDir "style"
	staticDir "js"
	staticDir "images"

app.get '/', (req, res) ->
	res.sendfile 'public/index.html', {root: __dirname + '/../'}

app.post '/backend', (req, res) ->
	data = req.body

	if data.create
		ts.addTask data.uqn, data.item.content

	if data.update
		ts.updateTask data.uqn, data.item

	if data.remove
		ts.deleteTask data.uqn, data.item.id
		
	if data.clearFinished
		ts.clearFinished data.uqn
	
	if data.clearAll
		ts.clearAll data.uqn
	
	if data.after
		ts.placeItemAfter activeList, data.item, if isNaN(data.after) then data.after else parseInt(data.after)
		
	#force send
	checkActiveList true
	
	res.send()
	
lists = []
tasks = []
activeList = false

checkActiveList = (sendRegardless = false) ->
	if activeList
		old = JSON.stringify tasks
		tasks = ts.getList activeList

		if old != JSON.stringify(tasks) or sendRegardless
			sendTasks()

sendTasks = ->
	io.sockets.emit 'tasks', tasks
			
sendLists = ->
	io.sockets.emit 'lists', lists

checkLists = ->
	newLists = ts.getLists()
	if newLists.length != lists.length
		lists = newLists
		sendLists()

#first time server starts we need to check lists
checkLists()

#this is initial message sent - start with sending lists 
io.sockets.on 'connection', (socket) ->
	sendLists()
	
	socket.on 'using-list', (data) ->
		if activeList != data.list
			tasks = []
			activeList = data.list
			
		checkActiveList true
		
#periodically we should scan for new task lists - say every 10 seconds
setInterval checkLists, 10000

#likewise check for new tasks - of course this only does anyhting if there is an activeList - fast - 2 seconds
setInterval checkActiveList, 200

#finally, lets start the server - replace the port w/ arg passed at some point
app.listen port, "127.0.0.1"

console.log "Running on port #{port}. Navigate to http://127.0.0.1:#{port} in your browser."