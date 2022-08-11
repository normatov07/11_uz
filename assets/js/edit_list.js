$(function(){
	
	THEFORM = $('#editlist_form');
	
	$('.editlist :checkbox').change(function(){
		if($(this).attr('checked')){			
			$(this).parents('.ofr').addClass('chkd');
		}else{
			$(this).parents('.ofr').removeClass('chkd');
		}
	}).click(function(){$(this).change()});
	
	$('#select_all').click(function(){
		$('.editlist :checkbox:not(:checked)').attr('checked','checked').change();
		return false;
	});
	
	$('#deselect_all').click(function(){
		$(".editlist :checkbox:checked").attr('checked',false).change();
		return false;
	});
	
	var mylistFormOptions = $.extend({}, ajaxOptions || {},
	{ 	
		success: function(json){
			if (!showErrors(json.errors, $('.mess', THEFORM))){
				
				showMessage(json.messages);
				
				if(json.data != null && json.data.act != null){
					
					disabled_amount = $(".editlist .disabled :checkbox:checked").length;
					enabled_amount = $(".editlist .enabled :checkbox:checked").length;
					deleted_amount = $(".editlist .deleted :checkbox:checked").length;
					checked_amount = $(".editlist :checkbox:checked").length;
					
					switch(json.data.act){
						case 'enable_selected':							
							
							if(json.data.mode != 'all' && json.data.mode != 'moder'){
								$(".editlist :checkbox:checked").parents('.ofr').remove();
							}else{
								$(".editlist :checkbox:checked").each(function(){
									$(this).attr('checked','');
									par = $(this).parents('.ofr');
									par.removeClass('chkd disabled expired deleted').addClass('enabled');
									$('.s i', par).html('Актуально до ').after('<p>'+json.data.dates[this.value]+'</p>').nextAll('p:not(:first)').remove();
								});
							}
							
							$('#count_enabled').text($('#count_enabled').text()*1+disabled_amount);
							$('#count_disabled').text($('#count_disabled').text()*1-disabled_amount);
							
						break;
						case 'disable_selected':						
						
							if(json.data.mode != 'all' && json.data.mode != 'moder'){
								$(".editlist :checkbox:checked").parents('.ofr').remove();
							}else{
								$(".editlist :checkbox:checked").each(function(){
									$(this).attr('checked','');	
									par = $(this).parents('.ofr');
									par.removeClass('chkd enabled expired deleted').addClass('disabled');
									$('.s i', par).html('Отключено').next('p').remove();
								});
							};
							
							$('#count_enabled').text($('#count_enabled').text()*1-enabled_amount);
							$('#count_disabled').text($('#count_disabled').text()*1+enabled_amount);
							
						break;
						case 'recover_selected':
							$('#count_deleted').text($('#count_deleted').text()*1-deleted_amount);
							$('#count_total, #count_disabled').text($('#count_total').text()*1+deleted_amount);
							$(".editlist :checkbox:checked").parents('.ofr').remove();
						break;
						case 'remove_selected':							
							$('#count_deleted').text($('#count_deleted').text()*1-deleted_amount);
							$(".editlist :checkbox:checked").parents('.ofr').remove();							
						break;
						case 'delete_selected':
						
							if(json.data.mode != 'moder'){
								$('#count_deleted').text($('#count_deleted').text()*1+checked_amount);
								$('#count_total').text($('#count_total').text()*1-checked_amount);
								$('#count_enabled').text($('#count_enabled').text()*1-enabled_amount);
								$('#count_disabled').text($('#count_disabled').text()*1-disabled_amount);
								$(".editlist :checkbox:checked").parents('.ofr').remove();
							}else{
								$(".editlist :checkbox:checked").each(function(){
									$(this).attr('checked','');	
									par = $(this).parents('.ofr');
									par.removeClass('chkd enabled expired disabled').addClass('deleted');
									$('.s i', par).html('Удалено').next('p').remove();
								});
							};
						
							
						break;
						case 'setread_selected':
							new_amount = $(".editlist .new :checkbox:checked").length;							
							$(".editlist :checkbox:checked").each(function(){																	 
								$(this).attr('checked','');											 
								$(this).parents('.ofr').removeClass('chkd new').addClass('read');
							});
							$('#new_messages').text('+'+($('#new_messages').text()*1-new_amount));
						break;
						default:
							if(json.redirect != null) location.href=json.redirect;
						break;
					}

					if($('.editlist .ofr').length == 0){
						$('.editlist').remove();
						$('.nolistitems').show();
					}
					
				}
			}
			$('input[type="submit"]', THEFORM).removeAttr('disabled');
		},
		beforeSubmit: function(){
			$('input[type="submit"]', THEFORM).attr('disabled','disabled');
		}
	});	
	
	THEFORM.ajaxForm(mylistFormOptions);	
	
	$(':input[name="recover_selected"], :input[name="delete_selected"], :input[name="remove_selected"]').click(function(){
		return(confirm('Вы уверены?'));
	});	
	
	$(':input[name="premium_selected"], :input[name="mark_selected"], :input[name="position_selected"]').click(function(){
		i=0; query = '';																										
		$(':checkbox:checked',THEFORM).each(
			function(){
				if(i > 0) query += '&';
				query += 'id[]='+this.value;
				i++;	
			}
		);
		if(query == '') showErrors('Вы не выбрали ни одного объявления!');
		else location.href='/my/payment/offer/'+ this.className+'/?' + query;
		return false;
	});	
	
});