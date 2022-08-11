$(function(){
	
	var THEFORM = $('#quickcontact');
	
	function contactsFormHandle(json) {
		if (showErrors(json.errors)) {			
			$('input[type="submit"][name="send"]', $('#quickcontact')).removeAttr('disabled');
			refreshCaptcha();
		} else {
			$('#ajaxstatus').html('Пожалуйста подождите...').show();
			if(json.redirect != null){				
				location.href=json.redirect;
			}else{
				location.href='/contacts/success/';
			}
			
		}
	}
	var contactsFormOptions = $.extend({}, ajaxOptions || {},
	{ 
		success: contactsFormHandle,
		beforeSubmit: function(){
//			$('input[type="submit"][name="send"]', $('#quickcontact')).attr('disabled','disabled');
		}
	});	
	
	if(useAjax){
		THEFORM.ajaxForm(contactsFormOptions);	
	}	

});