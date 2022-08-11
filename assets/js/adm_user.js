var URL = '/adm/user/';
var THEFORM;

function _handle_attached_file(json, file_code, pos, process_num_dt){

    var certificates_cont = THEFORM.find('#user_certificate');
    var obj_id = (typeof(json.obj) != 'undefined')?json.obj.id:json.data.obj_id;

    if (json.data['user_'+file_code].file != '') {
        certificates_cont
            .find('.file_link:eq('+pos+')')
                .find('a:eq(0)').attr('href', json.data['user_'+file_code].file).end()
                .find('a.del_cert_file').attr('href', '/adm/user/'+obj_id+'/delete_cert_file/'+file_code).end()
            .show();

        if (process_num_dt){
            certificates_cont
                .find('[name="'+file_code+'_num"]').val(json.data['user_'+file_code].num).closest('td').show().end().end()
                .find('[name="'+file_code+'_dt"]').val(json.data['user_'+file_code].dt).closest('td').show();
        }
    }
    else{
        certificates_cont
            .find('.file_link:eq('+pos+')')
                .find('a').removeAttr('href').end()
            .hide();

        if (process_num_dt){
            certificates_cont
                .find('[name="'+file_code+'_num"]').val('').closest('td').hide().end().end()
                .find('[name="'+file_code+'_dt"]').val('').closest('td').hide();
        }
    }
}

function fillFormWithObjValues(obj){
		THEFORM.clearForm();

		if(obj['status'] != 'disabled')	$(':input[name="status"] option[value="disabled"]', THEFORM).attr('disabled','disabled');
		else $(':input[name="status"] option[value="disabled"]:disabled', THEFORM).removeAttr('disabled');

		for(var i in obj){
			if( $( ':input[name="'+i+'"]', THEFORM ).length && obj[i] != null){
				$( ':input[name="'+i+'"]', THEFORM ).val(obj[i]);
			}
		}
}

function enableAjaxListLinks(){

	$('a','.userlist').click(function(){
		$('.this','.userlist').removeClass('this');
		var lnk = $(this);
		lnk.addClass('busy');
		$.getJSON(this.href, function(json){
			if(json.obj != null){

				fillFormWithObjValues(json.obj);

				if(json.data != null && json.data.role_title != null) $('#role_title').html(json.data.role_title);

				$('#registered').html(json.obj.registered);
				$('#simple_last_activity').html(json.obj.simple_last_activity+"<br/>\nIP: "+json.obj.last_ip);
				$('#gender').html(json.obj.gender);
				$('#offers_count').html(json.obj.offers_count?json.obj.offers_count:'0');
				$('#bonuses').html(json.obj.bonuses_count?json.obj.bonuses_count:'');
				$('#profile_view').attr('href',json.obj.url);
				$('#add_bonus').attr('href',json.obj.url_add_bonus);

				$(':checkbox[name="update_offer_status"]', THEFORM).attr('checked','checked');
				$('input[type="submit"]', THEFORM).removeAttr('disabled');

				$('.form_content', THEFORM).show().next().hide();

                if (typeof(json.data) != 'undefined'){

                    if (typeof(json.data.user_certificate) != 'undefined') {
                        _handle_attached_file(json, 'certificate', 0, 1);
                    }
                    if (typeof(json.data.user_license) != 'undefined'){
                        _handle_attached_file(json, 'license', 1, 1);
                    }
                    if (typeof(json.data.user_other) != 'undefined'){
                        _handle_attached_file(json, 'other', 2, 0);
                    }
                }
                else {
                    THEFORM.find('#user_certificate')
                        .find('.file_link')
                        .find('a').removeAttr('href').end()
                        .hide();
                }

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
	THEFORM = $('#user_form');

	var userFormOptions = $.extend({}, ajaxOptions || {}, {
		success: function(json){
			if (!showErrors(json.errors)) {

				showMessage(json.messages);

				if(json.redirect != null){
					$('#ajaxstatus').html('Пожалуйста подождите...').show();
					location.href = json.redirect;
				}

				if(json.data == null || json.data.act == null || json.data.act != 'save'){
					if(json.data == null){
						$('.this','.userlist').removeClass('this');
					}else if(json.data.act != null){
						if(json.data.act == 'delete'
						   || (json.data.act == 'enabled' && $(':input[name="status"]', THEFORM).val() == 'disabled')
						   || (json.data.act == 'disabled' && $(':input[name="status"]', THEFORM).val() == 'enabled')
						   || (json.data.act == 'banned' && $(':input[name="status"]', THEFORM).val() == 'enabled')
						){
							$('.this','.userlist').remove();
						}
					}

					$('.form_content', THEFORM).hide().next().show();
					THEFORM.clearForm();

				}
                else{
					$('input[type="submit"]', THEFORM).removeAttr('disabled');

                    if (typeof(json.data) != 'undefined'){
                        if (typeof(json.data.user_certificate) != 'undefined') {
                            _handle_attached_file(json, 'certificate', 0, 0);
                        }
                        if (typeof(json.data.user_license) != 'undefined'){
                            _handle_attached_file(json, 'license', 1, 0);
                        }
                        if (typeof(json.data.user_other) != 'undefined'){
                            _handle_attached_file(json, 'other', 2, 0);
                        }
                    }
                    else {
                        THEFORM.find('#user_certificate')
                            .find('.file_link')
                            .find('a').removeAttr('href').end()
                            .hide();
                    }
                    THEFORM.find('#user_certificate input:file').val('');

				}

			}else{
				$('input[type="submit"]', THEFORM).removeAttr('disabled');
			}
		},
		beforeSubmit: function(){
			$('input[type="submit"]', THEFORM).attr('disabled','disabled');
		}
	});

	var quicklistsearchFormOptions = $.extend({}, ajaxOptions || {}, {
		type: "GET",
		success: function(json){
			$('.busy', '#quicklistsearch').hide();
			if(json.data != null){

				if(!redrawList(json.data)) $('ul.list', '.rcol').html('<div class="note">Ничего не найдено</span>');
				$('.this','.viewmodes').removeClass('this');

			}
		},
		beforeSubmit: function(){
			$('.busy', '#quicklistsearch').show();
		}
	});

	function redrawList(json){
		if(json.list == null) return false;

		i = 0;
		listHtml = '';
		while(i < json.list.length){
			listHtml += '<li>';
			listHtml += '<a name="'+json.list[i][0]+'" href="' + URL + json.list[i][0] + '/';

			if(json.q != null)
				listHtml += '?q=' + json.q;
			else if(json.mode != null)
				listHtml += '?mode=' + json.mode;

			listHtml += '">' + json.list[i][1] + '</a>';

			if(json.list[i][2] != null) listHtml += ' <span>(' + json.list[i][2] + ')</span>';

			listHtml += '</li>'+"\n";

			i++;
		}


		$('ul.userlist').html(listHtml);

		if(json.pages != null) {
			if($('.pagination').length){
				$('.pagination').replaceWith(json.pages);
			}else{
				$('ul.userlist').after(json.pages);
			}
			enableAjaxPagination();
		}else{
			$('.pagination').remove();
		}

		enableAjaxListLinks();

		if(json.list.length == 0) return false;
		return true;
	};

	function enableAjaxPagination(){
		$('.pagination a').click(function(){
			$(this).addClass('busy');
			$.getJSON(this.href, function(json){
				if(json.data != null) redrawList(json.data);
			});
			return false;
		});
	};





	if(useAjax){

		$('a','.viewmodes').click(function(){
			$('.this','.viewmodes').removeClass('this');
			var lnk = $(this);
			lnk.addClass('busy');

			$.getJSON(this.href, function(json){
				if(json.data != null) redrawList(json.data);
				lnk.addClass('this');
				lnk.removeClass('busy');
			});

			return false;
		});

		THEFORM.ajaxForm(userFormOptions);

		$('#quicklistsearch').ajaxForm(quicklistsearchFormOptions);

		enableAjaxListLinks();
		enableAjaxPagination();

	}

	if($(':input[name="status"]').val() != 'disabled') $(':input[name="status"] option[value="disabled"]', THEFORM).attr('disabled','disabled');

    $('[name="certificate_scan"]', THEFORM).change(function(){
        $('[name="certificate_num"]', THEFORM).closest('td').show();
        $('[name="certificate_dt"]', THEFORM).closest('td').show();
    });
    $('[name="license_scan"]', THEFORM).change(function(){
        $('[name="license_num"]', THEFORM).closest('td').show();
        $('[name="license_dt"]', THEFORM).closest('td').show();
    });

    $('.del_cert_file').click(function(event){

        event.preventDefault();

        if (!confirm('Вы дествительно хотите удалить прикрепленный файл?')) return false;

        var id = THEFORM.find('[name="id"]').val();
        if (id){
            var cmd_node = $(this);
            var url = $(this).attr('href');
            if (url){
                $.getJSON(url, function(json){
                    if (json.errors){
                        showErrors(json.errors);
                    }
                    else{
                        cmd_node
                            .closest('.file_link')
                                .find('a').removeAttr('href').end()
                            .hide();

                        if (url.indexOf('certificate') != -1){
                            THEFORM.find('#user_certificate')
                                .find('[name="certificate_num"]').val('').closest('td').hide().end().end()
                                .find('[name="certificate_dt"]').val('').closest('td').hide();
                        }
                        if (url.indexOf('license') != -1){
                            THEFORM.find('#user_certificate')
                                .find('[name="license_num"]').val('').closest('td').hide().end().end()
                                .find('[name="license_dt"]').val('').closest('td').hide();
                        }
                        showMessage(json.messages);
                    }
                });
            }
        }
    });

});