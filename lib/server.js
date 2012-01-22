(function() {
  var activeList, app, checkActiveList, checkLists, express, io, lists, port, sendLists, sendTasks, staticDir, tasks, ts;

  express = require("express");

  app = express.createServer();

  io = require('socket.io').listen(app);

  ts = require("./taskshuffle");

  port = 1337;

  staticDir = function(dir) {
    return app.use("/" + dir, express.static(__dirname + "/../public/" + dir));
  };

  app.configure(function() {
    app.use(express.bodyParser());
    staticDir("chosen");
    staticDir("style");
    staticDir("js");
    return staticDir("images");
  });

  app.get('/', function(req, res) {
    return res.sendfile('public/index.html', {
      root: __dirname + '/../'
    });
  });

  app.post('/backend', function(req, res) {
    var data;
    data = req.body;
    if (data.create) ts.addTask(data.uqn, data.item.content);
    if (data.update) ts.updateTask(data.uqn, data.item);
    if (data.remove) ts.deleteTask(data.uqn, data.item.id);
    if (data.clearFinished) ts.clearFinished(data.uqn);
    if (data.clearAll) ts.clearAll(data.uqn);
    if (data.after) {
      ts.placeItemAfter(activeList, data.item, isNaN(data.after) ? data.after : parseInt(data.after));
    }
    checkActiveList(true);
    return res.send();
  });

  lists = [];

  tasks = [];

  activeList = false;

  checkActiveList = function(sendRegardless) {
    var old;
    if (sendRegardless == null) sendRegardless = false;
    if (activeList) {
      old = JSON.stringify(tasks);
      tasks = ts.getList(activeList);
      if (old !== JSON.stringify(tasks) || sendRegardless) return sendTasks();
    }
  };

  sendTasks = function() {
    return io.sockets.emit('tasks', tasks);
  };

  sendLists = function() {
    return io.sockets.emit('lists', lists);
  };

  checkLists = function() {
    var newLists;
    newLists = ts.getLists();
    if (newLists.length !== lists.length) {
      lists = newLists;
      return sendLists();
    }
  };

  checkLists();

  io.sockets.on('connection', function(socket) {
    sendLists();
    return socket.on('using-list', function(data) {
      if (activeList !== data.list) {
        tasks = [];
        activeList = data.list;
      }
      return checkActiveList(true);
    });
  });

  setInterval(checkLists, 10000);

  setInterval(checkActiveList, 200);

  app.listen(port, "127.0.0.1");

  console.log("Running on port " + port + ". Navigate to http://127.0.0.1:" + port + " in your browser.");

}).call(this);
