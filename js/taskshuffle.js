$(function(){
	window.TS = {
		name: false,
		backendUrl: function() { 
			return 'backend.php?file=' + TS.name;
		},
		timestamp: false,
		tasks: {},
		users: {},
		
		addTask: function(task) {
			$.post(TS.backendUrl(), {
				msg: task,
				user: 'test',
			});
		},
		
		drawUser: function(user) {
			TS.users[user] = {};
		},
		
		drawTask: function(task) {
			$('<li />')
				.html($('<button />').button({
						icons: {
							primary: 'ui-icon-check'
						}
					})
					.click(function() {
						$('div', $(this).parent()).addClass('TaskComplete');
						$(this).button('disable');
					}))
				.append($('<div />').text(task))
				.hide()
				.prependTo('.TaskList')
				.fadeIn('slow');
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

	         				TS.tasks[item.id] = item.msg;
							TS.drawTask(item.msg);
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
		
	};
	
	$('.NewTask').keypress(function(e) {
		if(e.keyCode == 13) {
			TS.addTask($(this).val());
			$(this).val('');
		}
	});
	
	$('.TaskList').sortable();
	$('.NewTask').focus();
	TS.connect();
});
