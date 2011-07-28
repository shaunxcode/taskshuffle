var keys = {
	ENTER: 13,
	ESCAPE: 27,
	SPACE: 32
};

$(function(){
	window.TS = {
		active: true,
		name: false,
		backendUrl: function() { 
			return 'backend.php?file=' + TS.name;
		},
		nameDefault: 'Start typing a new item here...',
		user: 'test',
		timestamp: false,
		tasks: {},
		users: {},
		
		showMan: function() {
			$('#man').animate({top: 167});
		},
		
		save: function(item) {
			$.post(TS.backendUrl(), {item: item});
		},
		
		drawUser: function(user) {
			TS.users[user] = {};
		},
		
		drawTask: function(item) {
			$('<li />')
				.addClass('Shadow')
				.attr('id', 'task-' + item.id)
				.html($('<div />').addClass('DragHandle'))
				.append($('<img />').attr('src', 'images/box_empty.png')
					.mousedown(function(){
						$(this).attr('src', 'images/box_transition_check.png');
					})
					.mouseup(function(){
						if($(this).attr('src') == 'images/box_transition_check.png') {
							$(this).attr('src', 'images/box_empty.png');
						}
					})
					.click(function() {
						item.complete = item.complete == 'true' ? 'false' : 'true';
						TS.toggleComplete(item);
						TS.save(item);
					}))
				.append($('<div />')
					.addClass('TaskContent')
					.html($('<span />')
						.text(item.task)
						.click(function() {
							if(TS.tasks[item.id].complete == 'true') {
								return;
							}
							
							var oldSpan = $(this).clone(true);
							var input = $('<input />')
								.attr('type', 'text')
								.val(TS.tasks[item.id].task)
								.addClass('Rounded')
								.keyup(function(e) {
									if(e.keyCode == keys.ENTER) {
										item.task = $(this).val();
										TS.save(item);
										$(this).replaceWith(oldSpan.text(item.task));
									}
									
									if(e.keyCode == keys.ESCAPE) {
										$(this).blur();
									}
								})
								.blur(function(){
									$(this).replaceWith(oldSpan);
								})
									
							$(this).replaceWith(input);
							input.focus();
						}))
					.append($('<img />').attr('src', 'images/trash.png').click(function(){
						item.deleted = true;
						TS.save(item);
						$('#task-' + item.id).hide(500, function() { $(this).remove() });
					})))
					
				.append($('<div />').addClass('clear'))
				.hide()
				.prependTo('.ActiveTasks')
				.fadeIn('slow');
		},
		
		toggleComplete: function(item) {							
			var view = $('#task-' + item.id);
			if(item.complete == 'true') { 
				if(!view.hasClass('TaskComplete')) {
					view.addClass('TaskComplete');
					$('> img', view).attr('src', 'images/box_checked.png');
					view.hide(500, function() { $(this).prependTo($('.CompletedTasks')).show(500); });
				}
				
				if(!$('.CompletionDate', view).length && item.completionDate) {
					$('.TaskContent', view).append(
						$('<span />')
							.addClass('CompletionDate')
							.text(item.completionDate)
							.css('float', 'right'));
				}
			} else {
				if(view.hasClass('TaskComplete')) {
					$('.CompletionDate', view).remove();
					view.removeClass('TaskComplete');
					$('> img', view).attr('src', 'images/box_empty.png');
					view.hide(500, function() { $(this).prependTo($('.ActiveTasks')).show(500); });
				}
			} 
		},
		
		connect: function() {
    	   $.get(
	    		TS.backendUrl(),
		       	{timestamp: TS.timestamp},
		       	function(response) {
					var updated = false;
					var ids = {};
	         		$.each(response.messages, function(i, item){
						ids[item.id] = true;
	         			if(!TS.tasks[item.id]) {
	         				if(!TS.users[item.user]) {
	         					TS.drawUser(item.user);
	         				}

	         				TS.tasks[item.id] = item;
							TS.drawTask(item);
							TS.toggleComplete(item);
							updated = true;
	         			}
	
						if(TS.tasks[item.id].task != item.task) {
							$('#task-' + item.id + ' .TaskContent span').text(item.task);
						}
						
						if(TS.tasks[item.id].complete != item.complete) {
							TS.toggleComplete(item);
							updated = true;
						}
						
						TS.tasks[item.id] = item;
	         		});
	
					if(updated) {
						TS.titleUpdate('updated...');
					}
					
					$.each(TS.tasks, function(taskId) {
						if(!ids[taskId]) {
							delete TS.tasks[taskId];
							$('#task-' + taskId).hide(500, function() { $(this).remove(); });
						}
					});
					
	         		TS.timestamp = response.timestamp;
	       		},
	      		'JSON')
	       		.complete(function() {
	         		// send a new ajax request when this request is finished
	           		setTimeout(function(){ TS.connect() }, 500); 
	       		});
   		},
		
		title: 'TaskShuffle',
		titleIntervalId: false,
		titleUpdate: function(msgText) {
			if(TS.titleIntervalId) {
				window.clearInterval(TS.titleIntervalId);
			}
			
			if(!TS.active) {
				var title = $('title');
				TS.titleIntervalId = window.setInterval(function() {
					if(title.text() == TS.title) {
						title.text(msgText);
					} else {
						title.text(TS.title);
					}
				}, 1000);
			}
		}
	};
	
	$(window)
		.focus(function() { 
			if(TS.titleIntervalId) {
				$('title').text(TS.title);
				window.clearInterval(TS.titleIntervalId);
			}
			TS.active = true;
		})
		.blur(function() {
			TS.active = false;
		})
	
	$('.NewTask')
		.keypress(function(e) {
			if(e.keyCode == keys.ENTER) {
				TS.save({
					user: TS.user, 
					task: $(this).val(), 
					complete: false});
				
				$(this).val('');
			}
		})
		.val(TS.nameDefault)
		.blur(function(){
			if($(this).val().length == 0) {
				$(this).val(TS.nameDefault);
			}
		})
		.focus(function(){
			if($(this).val() == TS.nameDefault) {
				$(this).val('');
			}
		});
	
	$('.ShuffleName')
		.keyup(function(e) {
			$(this).val($(this).val().replace(' ', '_'));
		});
		
	$('#clearFinished').click(function(){
		if(confirm('Are you sure you want to clear all finished tasks?')) {
			$.post(TS.backendUrl(), {clearFinished: true});
		}
	});
	
	$('#clearAll').click(function(){
		if(confirm('Are you sure you want to clear all tasks?')) {
			$.post(TS.backendUrl(), {clearAll: true});
		}
	});
	
	$('.ActiveTasks').sortable();
	
	$('#man').click(function(){$(this).animate({top: 220})});
	
	TS.code = '';
	$(window).keyup(function(e) {
		if(TS.code == '' && e.keyCode == 55) {
			TS.code = '7';
		} 
		else if(TS.code == '7' && e.keyCode == 52) {
			TS.code = '74'; 
		} 
		else if(TS.code == '74' && e.keyCode == 53) {
			TS.code = '745';
		}
		else if(TS.code == '745' && e.keyCode == 75) {
			TS.code = '745k';
		}
		else if(TS.code == '745k' && e.keyCode == 53) {
			TS.code = '';
			TS.showMan();
		}
		else {
			TS.code = '';
		}
	});
	
	TS.connect();
});