$(function(){
	window.TSS = {	
		lists: {},
		listViews: {},
		saving: {},
		
		createButton: function() {
			return $('<div />')
				.mousedown(function(){ $(this).addClass('ButtonDown') })
				.mouseup(function(){ $(this).removeClass('ButtonDown') })
				.mouseout(function(){$(this).removeClass('ButtonDown') });
		},
		
		createToggle: function(uqn, property, label) {
			return $('<div />')
				.addClass('ToggleGroup')
				.append($('<span />').addClass('ToggleLabel').text(label))
				.append($('<span />').addClass('Toggle' + property + ' Toggle ' + (TSS.lists[uqn][property] ? 'ToggleOn' : 'ToggleOff'))
					.mousedown(function(){
						$(this).addClass(TSS.lists[uqn][property] ? 'ToggleOnDown' : 'ToggleOffDown');
					})
					.click(function(){
						$(this)
							.removeClass(TSS.lists[uqn][property] ? 'ToggleOnDown ToggleOn' : 'ToggleOffDown ToggleOff')
							.addClass(TSS.lists[uqn][property] ? 'ToggleOff' : 'ToggleOn');

						TSS.lists[uqn][property] = !TSS.lists[uqn][property];
						
						var data = {uqn: uqn};
						data[property] = TSS.lists[uqn][property];
						TSS.saving[uqn][property] = true;
						$.post('backend.php', data, function(){
							TSS.saving[uqn][property] = false;
						});
					})
					.mouseup(function() {
						$(this).removeClass('ToggleOnDown ToggleOffDown');
					}));
		},
			
		drawList: function(list) {
			try { 
			var name = list.uqn.split('.').pop().replace(/_/g, ' ');
			return $('<div />')
				.addClass('container')
				.append(
					$('<div />')
						.addClass('span-11 first')
						.append($('<h3 />').text(name))
						.append($('<a />').text('View List').attr('href', '/' + list.uqn))
						.append($('<a />').text('Copy URL'))
						.append($('<div />')
							.addClass('ItemsTotal')
							.text(list.itemsTotal + ' Items')))
				.append($('<div />')
					.addClass('span-11')
					.append(TSS.createToggle(list.uqn, 'private', 'Private'))
					.append(TSS.createToggle(list.uqn, 'readOnly', 'Read Only'))
					.append(
						$('<div />').addClass('clear').text('Shared')))
				.append($('<div />').addClass('span-2 last').html(TSS.createButton().addClass('TrashList').click(function(){
					if(confirm("Are you sure you want to trash \"" + name + "\"")) {

					}		
				})))
				.hide()
				.appendTo('#listsContainer')
				.fadeIn('slow');
			}				
			catch (e) { console.log(e) }
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
							TSS.listViews[item.uqn] = TSS.drawList(item);
							TSS.saving[item.uqn] = {};
							updated = true;
	         			}
				
						if(TSS.lists[item.uqn].private != item.private && !TSS.saving[item.uqn].private) {
							$('.Toggleprivate', TSS.listViews[item.uqn]).removeClass('ToggleOn ToggleOff').addClass(item.private ? 'ToggleOn' : 'ToggleOff');
							updated = true;
						}
						
						if(TSS.lists[item.uqn].readOnly != item.readOnly && !TSS.saving[item.uqn].readOnly) {
							$('.TogglereadOnly', TSS.listViews[item.uqn]).removeClass('ToggleOn ToggleOff').addClass(item.readOnly ? 'ToggleOn' : 'ToggleOff');
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