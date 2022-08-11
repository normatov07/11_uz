$(function(){
//	AUTH Form related functions
	var AUTHFORM = $('form#auth');
	
	if(AUTHFORM.length){
		
		$('.x', AUTHFORM).click(function(){
			toggleAuth();
			AUTHFORM.fadeOut('fast');
			$("span.error", AUTHFORM).hide();
			$("span.proceed", AUTHFORM).hide();
			return false;
		});
		
		$(":submit", AUTHFORM).attr("disabled","disabled");
		
		$("#aemail,#apass",AUTHFORM).keyup(function(){ 
			 if($("#aemail",AUTHFORM).attr('value') != '' && $("form#auth #apass").attr('value') != ''){
				$(":submit",AUTHFORM).removeAttr("disabled");
			 }else{
				$(":submit",AUTHFORM).attr("disabled","disabled");
			 }		
		}).focus(function(){$(this).keyup()}).blur(function(){$(this).keyup()}).click(function(){$(this).keyup()});
		
		function toggleAuth(){
			
			if(AUTHFORM.is(':hidden')){
				AUTHFORM.show('fast').queue(function(){AUTHFORM.stop();$('#aemail',AUTHFORM).focus()});
			}else{
				$(AUTHFORM).hide('fast');
				$("span.error", AUTHFORM).hide();
				$("span.proceed", AUTHFORM).hide();
			};
			return false;
		};
	
		function loginHandle(json) {
	
			$("span.proceed", AUTHFORM).hide();
			if (json.errors != null && json.errors.length) {
				if($("span.error", AUTHFORM)[0] == null){
					$("#authStatus", AUTHFORM).append('<span class="error">' + json.errors[0]+'</span>');
				}
				$("span.error", AUTHFORM).show();
			} else {
				$("span.error", AUTHFORM).hide();
				$("span.proceed", AUTHFORM).text('Подождите...').show();
				location.href = location.href;
			}
			$(":submit",AUTHFORM).removeAttr("disabled");
		}
						
		loginOptions = $.extend({}, ajaxOptions || {},
		{ 
			success: loginHandle, 
			beforeSubmit: function() {
				if($("span.proceed", AUTHFORM)[0] == null) $("#authStatus", AUTHFORM).html('<span class="proceed">Проверка данных...</span>');
				$("span.error", AUTHFORM).hide();
				$("span.proceed", AUTHFORM).show();
				$(":submit", AUTHFORM).attr("disabled", "disabled");
			}
		});
		
		$(AUTHFORM).ajaxForm(loginOptions);
		$('.users #login').click(function(){return toggleAuth();});
	}
// END OF Auth Form	
});