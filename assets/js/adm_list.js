$(function(){
	$('#default_empty, #has_other').click(
		function(){
			$(this).next().attr('disabled', this.checked?'':'disabled');
		}
	);	   
});