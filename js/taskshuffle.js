$(function(){
	window.TS = {};
	
	$('.NewTask').keypress(function(e) {
		if(e.keyCode == 13) {
			$('.TaskList').prepend($('<li />')
				.html($('<button />').button({
						icons: {
							primary: 'ui-icon-check'
						}
					})
					.click(function(){
						
						$('div', $(this).parent()).addClass('TaskComplete');
						$(this).button('disable');
					}))
				.append($('<div />').text($(this).val())));
			$(this).val('');
		}
	});
	
	$('.TaskList').sortable();
	$('.NewTask').focus();
});
