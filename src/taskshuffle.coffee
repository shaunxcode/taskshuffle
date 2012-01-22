file = require "file"
path = require "path"
fs = require "fs"
crypto = require "crypto"

baseDir = process.cwd()

parseTask = (text) ->
	text = text.trim()

	task = id: false, status: 'pending', content: text, completed_on: false

	#is this a completed task?
	if text[0] is '#' 
		task.status = 'complete'

		#we always want to ignore the first char which is a hash
		task.content = text[1..-1]

		#if there is a hashpipe and a pipehash then we assume it is a task shuffle modified task
		if text.indexOf("#|") is 0 and text.indexOf("|#") > 0
			#we only want to get the first item preceding a pipehash so split by pipehash
			parts = task.content.split("|#")
			
			#we want to treat this as the date but we want to ignore first char as it is a pipe
			task.completed_on = parts.shift()[1..-1]
			
			#join the rest of the items by pipehash - in the case that a task a pipehash in it
			task.content = parts.join("|#").trim()

	#base the "id" on a hash based on the content - but after potentially stripping out ts meta data
	task.id = crypto.createHash('md5').update(task.content).digest('hex')
	task

exports.getLists = ->
	lists = []
	file.walkSync baseDir, (start, dirs, names) ->
		for name in names
			do (name) ->
				if name[-6..-1] is ".tasks"
					lists.push file.path.relativePath(baseDir, start)
	lists

exports.getList = getList = (list) ->
	tasks = []
	if path.existsSync list
		for line in fs.readFileSync("#{list}/.tasks", 'UTF-8').split "\n"
			do (line) ->
				if line.trim().length
					tasks.push parseTask(line)
	tasks

exports.setList = setList = (list, tasks) ->
	content = ""
	
	for task in tasks 
		do (task) ->
			line = task.content + "\n"
			if task.status is 'complete'
				if task.completed_on 
					line = "#|#{task.completed_on}|#" + line
				else
					line = "#" + line
			content += line

	fs.open "#{list}/.tasks", "w+", null, (err, fd) -> 
		fs.writeSync fd, content, 0

exports.deleteTask = (list, taskId) ->
	setList list, (task for task in getList(list) when task.id != taskId)
	
exports.addTask = (list, content) ->
	setList list, getList(list).concat([{content: content}])

exports.updateTask = (list, details) ->
	tasks = getList list
	for task, i in tasks
		do (task, i) ->
			if task.id is details.id
				tasks[i] = details

	setList list, tasks
	
exports.clearAll = (list) ->
	setList list, []
	
exports.clearFinished = (list) ->
	setList list, (task for task in getList(list) when task.status is 'pending')
	
exports.placeItemAfter = (list, item, afterItem) ->
	newList = []

	if afterItem is -1 then newList.push item
	
	for task in getList(list) 
		if task.id is item.id then continue
		do (task) ->
			newList.push task
			if task.id is afterItem
				newList.push item

	setList list, newList 