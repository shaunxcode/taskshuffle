$(function(){
	$('.NewTask').keypress(function(e) {
		if(e.keyCode == 13) {
			$('.TaskList').append($('<li />').text($(this).val()));
			$(this).val('');
		}
	});
	
	$('.TaskList').sortable();
});
