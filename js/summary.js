$(function(){
	window.TSS = {	
		lists: {},
		drawList: function(list) {
			$('<div />')
				.addClass('container')
				.append(
					$('<div />')
						.addClass('span-11 first')
						.append($('<h3 />').text(list.uqn.split('.').pop()))
						.append($('<a />').text('View List'))
						.append($('<a />').text('Copy URL'))
						.append($('<span />')
							.addClass('ItemsTotal')
							.text(list.itemsTotal + ' Items')))
				.append($('<div />')
					.append(
						$('<div />').addClass('ToggleGroup')
							.append($('span').addClass('ToggleLabel').text('Private'))
							.append($('span').addClass('Toggle TogggleOn')))
					.addClass('span-11'))
				.append($('<div />').addClass('span-2 last'))
				.hide()
				.appendTo('#listsContainer')
				.fadeIn('slow');
		},
		
		save: function(list) {
			$.post(
				'backend.php',
				{list: list});
		},
		
		connect: function() {
		   $.get(
	    		'backend.php', 
		       	{method: 'lists'}, 
		       	function(response) {
					var updated = false;
					var ids = {};
	         		$.each(response.lists, function(i, item) {
						ids[item.uqn] = true;
	         			if(!TSS.lists[item.uqn]) {
	         				TSS.lists[item.uqn] = item;
							TSS.drawList(item);
							updated = true;
	         			}
				
						TSS.lists[item.uqn] = item;
	         		});

					if(updated) {
						TS.titleUpdate('updated...');
					}
				
					$.each(TSS.lists, function(listId) {
						if(!ids[listId]) {
							delete TSS.lists[listId];
							$('#list-' + listId).hide(500, function() { $(this).remove(); });
						}
					});
					
	         		TS.timestamp = response.timestamp;
	       		},
	      	'JSON')
	       	.complete(function() {
	        	// send a new ajax request when this request is finished
	           	setTimeout(function(){ TSS.connect() }, 500); 
	       	})
		},
		nameDefault: 'Start a new list...'
	};
		
	$('.NewList')
		.keypress(function(e) {
			if(e.keyCode == keys.ENTER) {
				$('#newListPlus').click();
			}
		})
		.val(TSS.nameDefault)
		.blur(function(){
			if($(this).val().length == 0) {
				$(this).val(TSS.nameDefault);
			}
		})
		.focus(function(){
			if($(this).val() == TSS.nameDefault) {
				$(this).val('');
			}
		});
		
	$('#newListPlus')
		.mousedown(function(){
			$(this).attr('src', 'images/plus_2_down.png');
		})
		.mouseup(function(){
			if($(this).attr('src') == 'images/plus_2_down.png') {
				$(this).attr('src', 'images/plus_2.png');
			}
		})
		.mouseout(function(){
			$(this).mouseup();
		})
		.click(function() {
			var list = $('.NewList').val();
			if(list.length == 0 || list == TSS.nameDefault) {
				return;
			}
		
			TSS.save({name: list});

			$('.NewList').val('').focus();
		});
				
	TSS.connect();
});