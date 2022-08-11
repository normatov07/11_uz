var URL = '/adm/ro/edit/';
var THEFORM = $('#main_form');

$(function(){
	THEFORM = $('#main_form');
	
	var formOptions = $.extend({}, ajaxOptions || {}, {
		success: function(json){
			
			if (!showErrors(json.errors)) {
				showMessage(json.messages);
				if(json.data != null && json.data['save'] != null){
				
					$(':input[name="id"]', THEFORM).val(json.data['id']);
					
					if(json.data['pic'] != null){
						if($('#current_image')[0]){
							$('img','#current_image').replaceWith(json.data['pic']);
							$(':file','#current_image').val('');
						}else{
							html = '<tr id="current_image"><th class="note">Текущая:</th>'
							html += '<td>' + json.data['pic'] + '</td></tr>';						
							$('#upload_image').after(html);
						};
					};
					
				}else{
					location.href='/adm/ro/';
				};
			};
			
			$('input[type="submit"]', THEFORM).removeAttr('disabled');
		},
		beforeSubmit: function(formArray, jqForm, options){
			
			var	submitButton = null;
			
			$.each(formArray, function(i, val){
//				if($(':input[type="submit"][name="'+val.name+'"]')[0]) submitButton = val.name;
				if(submitButton = val.name.match(/update|save|dublicate/)) return;
			});
			
/*			if(submitButton != null && ){
				options.extraData[submitButton] = 1;
			}; */

			if(submitButton == 'dublicate'){
				$(':input[name="id"]', THEFORM).val('');
				$(':input[name="dublicate"]', THEFORM).hide();
				$(':input[name="status"][value="0"]').click().click();
				$('#current_image', THEFORM).remove();
				showMessage('Внимание! Данные сохранятся как дубликат.');
				return false;
			};
			
/*			var required = false;
			if(submitButton != null){
				$(':input.req', THEFORM).each(function(){
					if(this.value == ''){
						alert(this.name);
						required = true;
					};
				});
				if(required){
					showErrors('Не заполнены все обязательные поля!');
					return false;
				};
			}; */
			
//			$('input[type="submit"]', THEFORM).attr('disabled','disabled');
		}
	});

	if(useAjax){
		
		THEFORM.ajaxForm(formOptions);	
	
	}
	
	
	$('a','#categories').click(function(){
										
		$('select', $(this).parent().clone(true).insertAfter($(this).parent())).val('');
		
		$(this).unbind('click').click(function(){
		   $(this).parent().hide();//.remove();
		}).html('удалить');
		
		return false;
	});
	
//	$('.symbolsleft').prev().keyup(function(){ symbolsLeft(this);}).blur(function(){symbolsLeft(this)}).blur();

/**
 * List related
 */
 
 	function simpleAction(){
		var lnk = $(this);

		if(lnk.attr('className') == 'del' && !confirm('Вы уверены?')) return false;

		lnk.addClass('busy');

		$.getJSON(lnk.attr('href'), function(json){
			lnk.removeClass('busy');		 
			
			if(!showErrors(json.errors)){
								
				switch(lnk.attr('className')){
					case 'enable':
						$('span', $('th p:eq(1)',lnk.parents('tr')).removeClass().addClass(json.data.state)).html(json.data.state_title);
						lnk.hide();
						lnk.siblings('.disable').show();
					break;
					case 'disable':
						$('span', $('th p:eq(1)',lnk.parents('tr')).removeClass().addClass(json.data.state)).html(json.data.state_title);
						lnk.hide();
						lnk.siblings('.enable').show();
					break;
					case 'del':
						lnk.parents('tr').remove();
					break;					
				};
			};

		});
		
		return false;
	};

	$('a:not(.view)','.actions').click(simpleAction);

	$(':radio[name="redirect"]', THEFORM).click(function(){
		if(this.value == 'url'){
			$('#content').parents('tr').hide();
		}else{
			$('#content').parents('tr').show();
		}
	}).filter(':checked').click();
	
});