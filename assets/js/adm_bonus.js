var URL = '/adm/bonus/';
var THEFORM = $('#main_form');

function enableAjaxListLinks(){
	
	$('a','.userlist').click(function(){
		$('.this','.userlist').removeClass('this');
		var lnk = $(this);
		lnk.addClass('busy');
		$.getJSON(this.href, function(json){
			if(json.obj != null){
				
				$(':input[name="user_id"]',THEFORM).val(json.obj.id);
				$('h2',THEFORM).html(json.obj.name);
				$('.email a',THEFORM).attr('href', json.obj.url_edit);
				$('.email a',THEFORM).html(json.obj.email);
				$('#bonuses',THEFORM).html(json.obj.bonus_amount?json.obj.bonus_amount:0);
				
				THEFORM.filter(':hidden').show();
				
				lnk.parent().addClass('this');
			}else{
				showErrors(json.errors);
			}
			lnk.removeClass('busy');
		});	
		return false;
	});
};

$(function(){
	THEFORM = $('#main_form');
	
	var formOptions = $.extend({}, ajaxOptions || {}, {
		success: function(json){
			if (!showErrors(json.errors)) {
				showMessage(json.messages);
				if(json.obj.bonus_amount) $('#bonuses',THEFORM).html(json.obj.bonus_amount);
			}
			$('input[type="submit"]', THEFORM).removeAttr('disabled');
		},
		beforeSubmit: function(){
			if(!$(':input[name="user_id"]', THEFORM).val()){
				showErrors('Не выбран пользователь!');	
				return false;
			}
			$('input[type="submit"]', THEFORM).attr('disabled','disabled');
		}
	});

	if(useAjax){
		
		THEFORM.ajaxForm(formOptions);	
	
	}
	
	
	
	

});