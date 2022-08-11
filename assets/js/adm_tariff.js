$(function(){
	var THEFORM = $('#main_form');
	
	var formOptions = $.extend({}, ajaxOptions || {}, {
		success: function(json){
			if (!showErrors(json.errors)) { showMessage(json.messages); }
			$('input[type="submit"]', THEFORM).removeAttr('disabled');
		},
		beforeSubmit: function(){
			$('input[type="submit"]', THEFORM).attr('disabled','disabled');
		}
	});

	if(useAjax){
		
		THEFORM.ajaxForm(formOptions);	
	
	}
	
});