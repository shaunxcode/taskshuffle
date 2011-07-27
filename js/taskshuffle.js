$(function(){
	window.TS = {
		active: true,
		name: false,
		backendUrl: function() { 
			return 'backend.php?file=' + TS.name;
		},
		user: 'test',
		timestamp: false,
		tasks: {},
		users: {},
		
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
				.html($('<img />').attr('src', 'images/box_empty.png')
					.click(function() {
						item.complete = true;
						TS.save(item);
					}))
				.append($('<div />')
					.addClass('TaskContent')
					.html($('<span />').text(item.task))
					.append($('<img />').attr('src', 'images/trash.png')))
					
				.append($('<div />').addClass('clear'))
				.hide()
				.prependTo('.ActiveTasks')
				.fadeIn('slow');
		},
		
		toggleComplete: function(item) {
			if(TS.tasks[item.id].complete == item.complete) {
				return;
			}

			TS.tasks[item.id].complete = item.complete;
							
			var view = $('#task-' + item.id);
			if(item.complete == 'true') { 

				view.addClass('TaskComplete');
				$('> img', view).attr('src', 'images/box_checked.png');
				view.hide(500).prependTo($('.CompletedTasks')).fadeIn('slow');
			} else {
				view.removeClass('TaskComplete');
				$('> img', view).attr('src', 'images/box_empty.png');
			} 
		},
		
		connect: function() {
    	   $.get(
	    		TS.backendUrl(),
		       	{timestamp: TS.timestamp},
		       	function(response) {
					var updated = false;
	         		$.each(response.messages, function(i, item){
	         			if(!TS.tasks[item.id]) {
	         				if(!TS.users[item.user]) {
	         					TS.drawUser(item.user);
	         				}

	         				TS.tasks[item.id] = item;
							TS.drawTask(item);
							TS.toggleComplete(item);
							updated = true;
	         			}
	
						if(TS.tasks[item.id].complete != item.complete) {
							TS.toggleComplete(item);
							updated = true;
						}
	         		});
	
					if(updated) {
						TS.titleUpdate('updated...');
					}
					
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
			if(e.keyCode == 13) {
				TS.save({
					user: TS.user, 
					task: $(this).val(), 
					complete: false});
				
				$(this).val('');
			}
		})
		.val('Start typing a new item here...')
		.focus(function(){
			if(!$(this).data('firstTime')) {
				$(this).val('').data('firstTime', true);
			}
		})
	
	$('#clearFinished').click(function(){
		confirm('Are you sure you want to clear all finished tasks?');
	});
	
	$('#clearAll').click(function(){
		confirm('Are you sure you want to clear all tasks?');
	});
	
	$('.TaskList').sortable();
	TS.connect();
});