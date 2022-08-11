$(function(){
	
	var quickforms = [];
	
	var quickFormOptions = $.extend({}, ajaxOptions || {},
	{ 	
		success: function(json) {
			
			var newval;

			if(json.data != null){
				var form = $('#qf'+json.data.id+json.data.name);
			};
			
			hideLoad(form);
			
			if (showErrors(json.errors, $('.mess', this.form))) {	
				form.show();
			} else {
				
				if(json.data != null && json.data.value != null){
					if($(':input[name="'+json.data.name+'"]', form).is('select')){
						newval = $(':select[name="'+json.data.name+'"] option[value="'+json.data.value+'"]', form).text();	
					}else{
						newval = json.data.value;
					}
					form.prev().html(newval);
					if(json.data.title != null){
						form.prev().attr('title', json.data.title);
					};
				};
				
				form.hide().prev().show();
				form.remove();
			}
		},
		
		beforeSubmit: function(formArray, jqForm){
			if(formtimer[$(':input[name="quickedit_id"]', jqForm).val()]) 
				clearTimeout(formtimer[$(':input[name="quickedit_id"]', jqForm).val()]);

			showLoad($(jqForm));
			if(!isIE) $(jqForm).hide();
		}
	});			   
	
	function priceTypeChange(){
		par = $(this).parent();
		switch(this.value){
			case 'negotiated':
				$('.price_from, .price_to, .price, :input[name="currency"]', par).hide();
			break;
			case 'fixed':
				$('.price_from, .price_to', par).hide();
				$('.price,  :input[name="currency"]', par).removeClass('dn').show();
			break;
			case 'from-to':
				$('.price, .price_from, .price_to,  :input[name="currency"]', par).removeClass('dn').show();
			break;
		}
	}
	
	var formtimer = [];
	
	function quickEdit(obj){
		if(obj == null) obj = $(this);

		var ID = obj.parents('.ofr').attr('id').substr(1);
		
		var form = obj.next('form');
		
		if(form.length == 0){

			htmlForm = '<form method="post" action="/offer/quickedit/" id="qf'+ID+obj.attr('className')+'">';
			htmlForm += '<input type="hidden" name="quickedit_id" value="'+ID+'">';
			
			if(obj.hasClass('description')){
				htmlForm += '<textarea name="'+obj.attr('className')+'">'+obj.text()+'</textarea><br>';
				htmlForm += '<input type="submit" name="save" class="save" value="Сохранить">';
			}else if(obj.hasClass('price')){
				p_params = obj.attr('title').split('|');
				p_type = p_params[0];
				p_currency = p_params[1];
				
				if(p_params[2] == null){
					price = $('b:first', obj).text();
					price_to = $('b:eq(1)', obj).text();					
				}else{					
					price = '';
					price_to = $('b:first', obj).text();
				};		
				
				htmlForm += '<div class="price">Цена: ';
				htmlForm += form_Select('price_type', pt, p_params[0]);
				htmlForm += '<span class="price_from"> от </span>';
				htmlForm += '<input type="text" name="price" class="price" maxlength="12" title="Цена" value="'+price+'"';
				if(p_type == 'negotiated') htmlForm += ' class="dn"';
				htmlForm += '/> <span class="price_to"';
				htmlForm += '>до <input type="text" name="price_to" maxlength="12" title="Цена - до" value="'+price_to+'" /></span>';
				htmlForm += form_Select('currency', cur, p_currency);
				htmlForm += '<input type="submit" name="save" class="ok" value="ОК">';
				htmlForm += '</div>';
				
			}else{
				htmlForm += '<input type="text" name="'+obj.attr('className')+'" class="inp" value="'+obj.text()+'">';
				htmlForm += '<input type="submit" name="save" class="ok" value="ОК">';
			}
			
			htmlForm +='<a name="mess"/><div class="mess bcorns" style="display: none;"><i class="ct"><i/><b/></i><div/><i class="cb"><i/><b/></i></div>';			
			htmlForm +='</form>';
			
			form = obj.after(htmlForm).next('form');		
			if(obj.hasClass('price')){$(':input[name="price_type"]', form).change(priceTypeChange).change()};
		}
		obj.hide();
		form.show();
				
		form.ajaxForm(quickFormOptions);
		formtimer[ID] = '';
		$(':text, :input[type="textarea"], select', form).blur(function(){formtimer[ID] = setTimeout(function(){form.remove(); obj.show()}, 150);}).focus(function(){clearTimeout(formtimer[ID]);}).filter(':first').focus();
		
	}
 
	$('.title, div.price, .description', '.offerlist').click(function(){quickEdit($(this))});
});	