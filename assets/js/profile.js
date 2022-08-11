var THEFORM;
$(function(){
		   
	THEFORM = $('#profile_form');
	
	var formOptions = $.extend({}, ajaxOptions || {}, {
		beforeSubmit: function(){
			$('.bcorns, .buttons', THEFORM).hide();
			THEFORM.after('<div class="loading tmar" id="loading">Пожалуйста, подождите...</div>');
			$('input[type="submit"]', THEFORM).attr('disabled','disabled');
		},
		success: function(json){
			
			//THEFORM.show();
			$('#loading').remove();
			
			if (!showErrors(json.errors)) {
				showMessage(json.messages);
			}else{
				$('input[type="submit"]', THEFORM).removeAttr('disabled');
			}
			
			THEFORM.after('<div class="tmar"><a href="/my/settings/" onclick="$(\'.ftable, .buttons\', THEFORM).show(); hideMessage(); $(this).parent().remove(); return false">Продолжить изменение настроек →</a></div>');
		}
	});

	if(useAjax){
		$(THEFORM).ajaxForm(formOptions);	
	}	

});