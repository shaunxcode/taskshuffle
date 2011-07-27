$(function(){
	window.TS = {
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
				.attr('id', 'task-' + item.id)
				.html($('<button />').button({
						icons: {
							primary: 'ui-icon-check'
						}
					})
					.click(function() {
						item.complete = true;
						TS.save(item);
					}))
				.append($('<div />').text(item.task))
				.hide()
				.prependTo('.TaskList')
				.fadeIn('slow');
		},
		
		toggleComplete: function(item) {
			$('#task-' + item.id + ' div')[(item.complete == 'true' ? 'add' : 'remove') + 'Class']('TaskComplete');
		},
		
		connect: function() {
    	   $.get(
	    		TS.backendUrl(),
		       	{timestamp: TS.timestamp},
		       	function(response) {
	         		$.each(response.messages, function(i, item){
	         			if(!TS.tasks[item.id]) {
	         				if(!TS.users[item.user]) {
	         					TS.drawUser(item.user);
	         				}

	         				TS.tasks[item.id] = item;
							TS.drawTask(item);
	         			}
						TS.toggleComplete(item);
	         		});

	         		TS.timestamp = response.timestamp;
	       		},
	      		'JSON')
	       		.complete(function() {
	         		// send a new ajax request when this request is finished
	           		setTimeout(function(){ TS.connect() }, 500); 
	       		});
   		},
		
	};
	
	$('.NewTask').keypress(function(e) {
		if(e.keyCode == 13) {
			TS.save({
				user: TS.user, 
				task: $(this).val(), 
				complete: false});
				
			$(this).val('');
		}
	});
	
	$('.TaskList').sortable();
	$('.NewTask').focus();
	TS.connect();
});