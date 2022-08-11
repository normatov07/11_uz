var URL = '/adm/payment/';
var THEFORM = $('#main_form');


$(function(){
	THEFORM = $('#main_form');
	URL = THEFORM.attr('action');

	$('.num','.payments').bind('mouseover', DDM_open);
    $('.num','.payments').bind('mouseout', DDM_timer);

	function replaceStatusSelect(st, withVal){
		st.html(withVal);
		st.click(enableStatusSelect);
	}
	
	var formtimer = [];

	function enableStatusSelect(){
		
		var st = $(this);
		var par = st.parents('tr');	
		var status_select = $('#status','.listfilter').clone();
		
		st.unbind('click');						   
		
		ID = par.attr('id').substr(1);

		html = '<form action="'+URL+'change_status/" method="post" id="sc'+ID+'">';
		html += '<input type="hidden" value="'+ID+'" name="id">';
		html += '</form>';
		html += '<span class="prev_status dn">' + st.html() + '</span>';

		st.html(html);
		$('option[value=""]',status_select).remove();
		
		status_select.val(par.attr('className'));
		$('form',st).append(status_select);
		
		status_select.blur(function(){formtimer[ID] = setTimeout(function(){replaceStatusSelect(st, $('.prev_status',st).html());}, 150);}).focus(function(){clearTimeout(formtimer[ID]);}).focus();

		status_select.change(function(){
			if(confirm('Вы действительно хотите сменить статус?')){
				$(status_select).unbind('blur');
				
				var changeStatusOptions = $.extend({}, ajaxOptions || {}, {
					success: function(json){
						if (!showErrors(json.errors)) {
							if(json.data != null && json.data.status != null){
								this.par.attr('className', json.data.status);
								replaceStatusSelect(st, json.data.status_title);
							}else{
								replaceStatusSelect(this.st, $('.prev_status',this.st).html());
							}
						}
					},
					beforeSubmit: function(){
						this.st = st;
						this.par = par;
						$('form',st).hide().after('<span class="loading">подождите...</span>');
					}
				});

				$('form',st).ajaxForm(changeStatusOptions).submit();	
				
				
			}else{
				st.html($('span',st).html());
			};
		});
		
	}

	$('.s','.payments').click(enableStatusSelect);

});