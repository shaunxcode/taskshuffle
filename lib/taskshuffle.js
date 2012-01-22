(function() {
  var baseDir, crypto, file, fs, getList, parseTask, path, setList;

  file = require("file");

  path = require("path");

  fs = require("fs");

  crypto = require("crypto");

  baseDir = process.cwd();

  parseTask = function(text) {
    var parts, task;
    text = text.trim();
    task = {
      id: false,
      status: 'pending',
      content: text,
      completed_on: false
    };
    if (text[0] === '#') {
      task.status = 'complete';
      task.content = text.slice(1);
      if (text.indexOf("#|") === 0 && text.indexOf("|#") > 0) {
        parts = task.content.split("|#");
        task.completed_on = parts.shift().slice(1);
        task.content = parts.join("|#").trim();
      }
    }
    task.id = crypto.createHash('md5').update(task.content).digest('hex');
    return task;
  };

  exports.getLists = function() {
    var lists;
    lists = [];
    file.walkSync(baseDir, function(start, dirs, names) {
      var name, _i, _len, _results;
      _results = [];
      for (_i = 0, _len = names.length; _i < _len; _i++) {
        name = names[_i];
        _results.push((function(name) {
          if (name.slice(-6) === ".tasks") {
            return lists.push(file.path.relativePath(baseDir, start));
          }
        })(name));
      }
      return _results;
    });
    return lists;
  };

  exports.getList = getList = function(list) {
    var line, tasks, _fn, _i, _len, _ref;
    tasks = [];
    if (path.existsSync(list)) {
      _ref = fs.readFileSync("" + list + "/.tasks", 'UTF-8').split("\n");
      _fn = function(line) {
        if (line.trim().length) return tasks.push(parseTask(line));
      };
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        line = _ref[_i];
        _fn(line);
      }
    }
    return tasks;
  };

  exports.setList = setList = function(list, tasks) {
    var content, task, _fn, _i, _len;
    content = "";
    _fn = function(task) {
      var line;
      line = task.content + "\n";
      if (task.status === 'complete') {
        if (task.completed_on) {
          line = ("#|" + task.completed_on + "|#") + line;
        } else {
          line = "#" + line;
        }
      }
      return content += line;
    };
    for (_i = 0, _len = tasks.length; _i < _len; _i++) {
      task = tasks[_i];
      _fn(task);
    }
    return fs.open("" + list + "/.tasks", "w+", null, function(err, fd) {
      return fs.writeSync(fd, content, 0);
    });
  };

  exports.deleteTask = function(list, taskId) {
    var task;
    return setList(list, (function() {
      var _i, _len, _ref, _results;
      _ref = getList(list);
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        task = _ref[_i];
        if (task.id !== taskId) _results.push(task);
      }
      return _results;
    })());
  };

  exports.addTask = function(list, content) {
    return setList(list, getList(list).concat([
      {
        content: content
      }
    ]));
  };

  exports.updateTask = function(list, details) {
    var i, task, tasks, _fn, _len;
    tasks = getList(list);
    _fn = function(task, i) {
      if (task.id === details.id) return tasks[i] = details;
    };
    for (i = 0, _len = tasks.length; i < _len; i++) {
      task = tasks[i];
      _fn(task, i);
    }
    return setList(list, tasks);
  };

  exports.clearAll = function(list) {
    return setList(list, []);
  };

  exports.clearFinished = function(list) {
    var task;
    return setList(list, (function() {
      var _i, _len, _ref, _results;
      _ref = getList(list);
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        task = _ref[_i];
        if (task.status === 'pending') _results.push(task);
      }
      return _results;
    })());
  };

  exports.placeItemAfter = function(list, item, afterItem) {
    var newList, task, _fn, _i, _len, _ref;
    newList = [];
    if (afterItem === -1) newList.push(item);
    _ref = getList(list);
    _fn = function(task) {
      newList.push(task);
      if (task.id === afterItem) return newList.push(item);
    };
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      task = _ref[_i];
      if (task.id === item.id) continue;
      _fn(task);
    }
    return setList(list, newList);
  };

}).call(this);
