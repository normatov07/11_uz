$(function(){
	var THEFORM ;	

	var formOptions = $.extend({}, ajaxOptions || {}, {
		beforeSubmit: function(){
			$('input[type="submit"]', THEFORM).attr('disabled','disabled');
		},
		success: function(json){
			if (!showErrors(json.errors)) {
				showMessage(json.messages);
				if(json.redirect != null){
					THEFORM.hide();
					THEFORM.after('<div class="loading tmar" id="loading">Пожалуйста, подождите...</div>');
					location.href = json.redirect;
				}
			}else{
				$('input[type="submit"]', THEFORM).removeAttr('disabled');
			}
		}
	});

	dotRE = /(\d)\./;
	dotReplacement = '$1,';
	function changePayment(){

		var selected_count = $(':input[name="count"]',THEFORM).val() || 1;
		var obj_amount = $(':input[name="id[]"]', THEFORM).length || 1;
		
		var total_price = null;
		
		$(':input[name="method_id"]',THEFORM).each(function(){

			price = (selected_count * methods[this.value]['price'] * obj_amount).toFixed(2);
			
			if(price == Math.round(price)) price *= 1;
			
			price_html = declension_numerals(price, methods[this.value]['currency']).replace(dotRE,dotReplacement);
			
			$('span', $(this).parents('li')).html('('+ price_html +')');
			
			if(this.checked){
				if((userDiscount = $('#discount').text()) && methods[this.value]['discount'] == true){
					var discount = price * userDiscount / 100;			
					discounted_price = price - discount;
					discounted_price_html = declension_numerals(discounted_price, methods[this.value]['currency']).replace(dotRE,dotReplacement);
				}else{
					userDiscount = null;
					discount = null;
				};
				
				if(methods[this.value]['exchange_rate'] != null){
					total_price = declension_numerals((price * methods[this.value]['exchange_rate']).toFixed(2) , methods[this.value]['exchange_currency']).replace(dotRE,dotReplacement);
					if(userDiscount != null){
						discount = declension_numerals((discount * methods[this.value]['exchange_rate']).toFixed(2) , methods[this.value]['exchange_currency']).replace(dotRE,dotReplacement);
						discounted_total = declension_numerals((discounted_price * methods[this.value]['exchange_rate']).toFixed(2) , methods[this.value]['exchange_currency']).replace(dotRE,dotReplacement);
					};
					
				}else{
					total_price = price_html;
					if(userDiscount != null && discount != null){
						discounted_total = discounted_price_html;
					};
				};
				$('#unit_cost').html(declension_numerals(methods[this.value]['price'], methods[this.value]['currency']).replace(dotRE,dotReplacement));
				
				if(discount != null){					
					$('#raw_price').html(total_price).parent().show().removeClass('dn');
					$('#discount_price').html(discount).parent().show().removeClass('dn');
					$('#total_price').html(discounted_total);
				}else{
					$('#raw_price').html(total_price).parent().hide();
					$('#discount_price').html(discount).parent().hide();
					$('#total_price').html(total_price);
				};
				
				$('#comment').html(methods[this.value]['comment']);
				
				if($('#method_note').length && methods[this.value]['description'] != null) $('#method_note').html(methods[this.value]['description']);
			}
		});
	
	}
	
	function pageReload(){
		THEFORM.hide();
		THEFORM.after('<div class="loading tmar" id="loading">Пожалуйста, подождите...</div>');
		$.post( THEFORM.attr('action'), THEFORM.formToArray(), function(html){
			$('#main').replaceWith(html);
			modifyRadios();	 
			modifySelects();
			decorateErrors();
			modifyAjaxStatus();
			init();
		});		
	}

	function init(){
		
		THEFORM = $('#payment');
		$('.payment', THEFORM).removeClass('dn');
		$(':input[name="service_id"]',THEFORM).change(pageReload);
		$(':input[name="count"]',THEFORM).change(changePayment);
		$(':input[type="text"][name="count"]',THEFORM).keyup(function(){changePayment()});
		$(':input[name="method_id"]',THEFORM).click(changePayment);
		
		if(useAjax){ THEFORM.ajaxForm(formOptions);	};

	}
	
	init();
});