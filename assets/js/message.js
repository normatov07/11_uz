$(function(){
	   
  	var THEFORM = $('form[name="message_reply"]');
		   
	function addFormHandle(json) {
		if (showErrors(json.errors)) {			
			$('input[type="submit"][name="send"]', THEFORM).removeAttr('disabled');
		} else {
			$('#ajaxstatus').html('Пожалуйста подождите...').show();
			if(json.redirect != null){				
				location.href=json.redirect;
			}else{				
				location.href='/my/messages/outbox/?success';
			}
			
		}
	}
		
	var addFormOptions = $.extend({}, ajaxOptions || {},
	{ 
		success: addFormHandle,
		beforeSubmit: function(){
			$('input[type="submit"][name="send"]', THEFORM).attr('disabled','disabled');
		}
	});	
	
	
	if(useAjax){
		THEFORM.ajaxForm(addFormOptions);	
	}	

	$('textarea', '#reply').keyup(function(){ 
															
			 if($(this).attr('value') != ''){
				$(':input[type="submit"][name="send"]', '#reply').removeAttr("disabled");
			 }else{
				$(':input[type="submit"][name="send"]', '#reply').attr("disabled","disabled");
			 }		
			 
		}).blur(function(){$(this).keyup()});//.click(function(){$(this).keyup()});
	
	$(':input[type="submit"][name="send"]', '#reply').attr('disabled','disabled');
	$(':input[type="submit"][name="cancel"]', '#reply').click(function(){location.href="/my/messages/outbox/"; return false;});

//	$(':input[name="content"]','form[name="message_reply"]').keyup(symbolsLeft).focus();

});