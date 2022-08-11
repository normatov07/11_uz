var useAjax = true;

var ajaxOptions = {

///	test: 'yahooo',

	dataType: 'json',

	type: "POST",

//	beforeSubmit: function(){alert('aaa')},

	success: function(responseText){

		showErrors(responseText.errors);

		showMessage(responseText.messages);

	},

	error: function(request, errorStr, exceptObj){

		if(errorStr == 'parsererror') showErrors('<b>Incorrect Ajax response:</b><br>' + request.responseText);

		else showErrors(errorStr+': '+request.responseText);



		$(':submit:disabled, :image:disabled').removeAttr('disabled');

	}

};



//	var myLoad = function(hash){ $('form',hash.w).ajaxForm(); }; 

var modalOptions = {

//	ajax: '@href', 

	trigger: 'a.modal',

//	overlay: 70,

	closeClass: 'jqmClose',

	target: '.jqmContent',

//	onLoad:myLoad,

	modal: true,

	toTop: true,

	test: false,

	onShow: function(h){

		$('.jqmContent', h.w).html('<div class="loading">идёт загрузка...</div>');

		$(document).keydown(function (e) {if((e.which == 27)&&(h.w.filter(':visible').size() > 0)) $('.jqmClose').click();});

		modalPosition();

		h.w.show().focus();

	}

};



function modalPosition(modal, height){

	

	if(ie6) return;

	

	if(modal == null) modal = $('.jqmWindow');

	if(height == null) height = modal.height();

	

	if(screenHeight() < height){

		modal.height(screenHeight() - 20);

		modal.css('top', '5px');

	}else{

		modal.css('top', Math.round((screenHeight() - height) / 2) + 'px');

	}

	return modal.height();

}





$(function() {



	$('form').bind("form-pre-serialize", function(){ 

	  if($(this).children(':hidden[name="is_ajax"]').length == 0) $(this).prepend('<input type="hidden" name="is_ajax" value="1">');

	});



	modifyCheckboxes();	   

	modifyRadios();	 

	modifySelects();

	decorateErrors();

	modifyCaptcha();

	modifyAjaxStatus();

	modifySymbolsleft();

	

	$('.toggleVal').each(addToggleValue);



	isIE=$.browser.msie;

	ie6=$.browser.msie&&($.browser.version == "6.0");



});



/**

 * MESSAGING

 */



function showMessage(messages, mess, mode) {

	

	if(messages == null) return 0;

	else if(typeof messages == 'string') messages = [messages];



	if(mess == null) var mess = $('.mess');

	else if(typeof mess != 'object') var mess = $('#'+mess);

	

	if(mode == null) mode = '';

	

	var form = mess.parents('form');

	

	if(mess.length != null)	hideMessage(mess);

	

	if (messages.length) {

		if(mode == 'error'){

			var messageString = '<h3>Внимание ошибка:</h3> ';

			var errRe = /"([a-z0-9\[\]_]+)"/;

		}else{

			var messageString = '';

		}

		messageString += '<ul>';

		

		$(messages).each(function(){

			mes = this;

			if(mode == 'error' && form != null) {

				if(errRe.exec(this)){		

					fieldname = RegExp.$1;

					mes = this.replace(errRe, '<a href="#" onmouseover="fieldHover(\'' + fieldname +'\',\''+form.attr('name')+'\')" onmouseout="fieldHover(\'' + fieldname +'\',\''+form.attr('name')+'\')" onclick="return fieldFocus(\'' + fieldname +'\',\''+form.attr('name')+'\')">'+Field(fieldname, form).attr('title')+'</a>');

				}

			}

			

			messageString += '<li>' + mes + '</li>';

		});

		

		messageString += '</ul>';

		

		if(mess[0] != null){

			if(mode == 'error') $(mess).addClass('error');

			else $(mess).removeClass('error');

			$('div',mess).html(messageString).parent().slideDown('fast');

		}else{

			alert(strip_tags(messageString));

		}

	}

	

	return Boolean(messages.length);

}



function hideMessage(mess){

	if(mess == null) var mess = $('.mess');

	else if(typeof mess != 'object') var mess = $('#'+mess);

	mess.hide();

}



function showErrors(errors, mess) {

	return showMessage(errors, mess, 'error');

}



function decorateErrors(mess) {

	var errRe = /"([a-z0-9_]+)"/;



	if(mess == null) var mess = $('.mess');

	else if(typeof mess != 'object') var mess = $('#'+mess);



	var form = mess.parents('form');

	

	if(form[0] == null) return;

	

	var errors = $('div > ul',mess).children();

		

	if (errors.length) {

		$(errors).each(function(){

			err = $(this).html();

			errRe.exec(err);		

			fieldname = RegExp.$1;

			title = Field(fieldname, form).attr('title');

			if(title == null) title = $('#'+fieldname, form).attr('title');

			if(title != null) $(this).html(err.replace(errRe, '<a href="#" onmouseover="fieldHover(\'' + fieldname +'\',\''+form.attr('name')+'\')" onmouseout="fieldHover(\'' + fieldname +'\',\''+form.attr('name')+'\')" onclick="return fieldFocus(\'' + fieldname +'\',\''+form.attr('name')+'\')">'+(title != ''?title:fieldname)+'</a>'));

		});

	}

	

	return;

}





/** 

 * AJAX related functions 

 */



function showLoad(obj){

	hideLoad(obj);

	if(!obj.next().hasClass('loading')) obj.after(' <span class="loading">подождите...</span>');

}

function hideLoad(obj){

	if(obj.next().hasClass('loading')) obj.next().remove();

}



/**

 *  FORMS related functions

 */



/**

 * Function guessing Form field title

 */



function refreshCaptcha() {

	$('#captcha').click();

	$('#captcha_code').val('');

}



function setFieldTitle(field){

	

	var title = field.attr('name');

	var guessedtitle = '';

	if(field.attr('title') == ''){

		if(field.parent().attr('tagName') == 'label'){

			guessedtitle = field.parent().text();

		}



		if(guessedtitle == '' && field.prev()[0] != null){

			switch(field.prev().attr('tagName')){

				case 'H5':

				case 'label':

					guessedtitle = field.prev().text();

				break;

			}

		}

		

		if(guessedtitle == '' && field.parents('td')){

			guessedtitle = field.parent().parent().children('th:first-child').text();

		}

		

		if(guessedtitle != '') title = guessedtitle;

	}

	

	var re = new RegExp('/[\*:]/','i');

	title = strip_tags(title).replace(/[^а-я\w\s]/ig,'');

	field.attr('title', title);

}



/**

 * Function returns Form field by it's name

 */



var squareBraketsRemove = /\[|\]/g;



function Field(field, form){

	if(typeof field == 'string'){

		var fieldname = field;

		if(form != null && typeof form == 'string') form = $('form[name="'+form+'"]:first');

		if(form != null) field = $(':input[name="'+fieldname+'"]:first',form);

		else field = $(':input[name="'+fieldname+'"]:first');

		if(field == null || field[0] == null){

			nobraketsname = fieldname.replace(squareBraketsRemove,'');

			if($('#' + nobraketsname, form).length) field = $('#' + nobraketsname, form);

			else if($('#' + fieldname, 'form').attr('title') != null) field = $('#' + fieldname, 'form');

		};

	};

	

	if((field == null || field[0] == null) && fieldname != null){ field = $('<div></div>'); field.attr('title', fieldname); return field};



	if(field.attr('title') == '') setFieldTitle(field);

	return field;

}





function fieldHover(field, form){

	field = Field(field, form);

	if(field.attr('type') != 'radio' && field.attr('type') != 'checkbox') field.toggleClass('hover');	

}



function fieldFocus(field, form){

	field = Field(field, form);

	field.focus();

	return false;

}





function check(checkbox){

	if($(checkbox).attr('checked')){

		$(checkbox).parent('label').addClass('checked');

		$(checkbox).filter('.draft').parent('label').addClass('r');

	}else{ $(checkbox).parent('label').removeClass('checked r'); }

	label(checkbox);	

}



function checkAll(parent){

	parent.children('label').each(function(){$(this).children(':checkbox').each(function(){if(!this.checked) $(this).click(); check(this)})});

	parent.children(':checkbox').each(function(){if(!this.checked) $(this).click(); check(this)});

	return false;

}



function symbolsLeft(textarea, maxSymbols, output){

	var textarea = $(textarea);



	if (!textarea.is('textarea,:text')) {

			var textarea = $(this);

			if (!textarea.is('textarea,:text')) {

					return;

			}

	}



	var output = output || $('.symbolsleft > b', textarea.parent()) || textarea.siblings('.symbolsleft:first > b');

			

	if(!maxSymbols){

			if((maxSymbols = textarea.data('maxSymbols')) == null){

					maxSymbols = Number(output.text());

					textarea.data('maxSymbols', maxSymbols);

			}

	}



	var curVal = textarea[0].value || '';



	output.html(String (maxSymbols - curVal.length));

	textarea.needRefresh = false;			



	var parent = output.parent();

	if(curVal.length > maxSymbols){

			if(!parent.children('.error').show().length) {

					parent.prepend('<span class="error bd">Внимание! </span>');

			}

	} else {

			parent.children('.error').hide();

	}



	return (curVal.length <= maxSymbols);		

}



function modifySymbolsleft(){

	$('.symbolsleft').prev().keyup(function(){ symbolsLeft(this);}).blur(function(){symbolsLeft(this)}).blur();

}



/**

 * Generates select dropdown from array

 */

function form_Select(name, listArray, current, emptyTitle, required, id){

	sel = '<select name="'+name+'" id="'+(id != null? id:name)+'"'+(required != null && required != false ? ' class="req"':'')+'>';

	if(emptyTitle != null) sel += '<option value="" selected="selected" class="dis">'+emptyTitle+'</option>';

	i = 0;

	while(i<listArray.length){

		value = listArray[i].value != null?listArray[i].value:listArray[i].id;

		text = listArray[i].text != null?listArray[i].text:listArray[i].title;		

		sel += '<option value="'+value+'"';

		if((current == null && i == 0 && emptyTitle == null) || (current != null && current == value)) sel += ' selected="selected"';

		sel += '>'+text+'</option>';

		i++;

	}		

	sel += '</select>';

	return sel;	

}



/** 

 * Field value toggle related functions

 */



function guessToggleFieldTitle(field){

	if((field == null || typeof field != 'object') && this.type == 'text') field = this;

	if(field.title == null || field.title == '') field.title = field.value;	

}



function setToggleFieldClass(field){

	if((field == null || typeof field != 'object') && this.type == 'text') field = this;

	if(field.title == field.value) $(this).addClass('lgr');	

}



function toggleValue(){

	if(this == null || this.type != 'text') return true;



	$(this).removeClass('lgr');

	guessToggleFieldTitle(this);



	this.select();

	if(this.value == this.title) this.value = "";

  

	$(this).blur(function(){ if(this.value == "") {this.value=this.title; $(this).addClass('lgr');}});

	return true;

};



/**

 * form field behaviour modifiers

 */



function addToggleValue(field){	

	if((field == null || typeof field != 'object') && this.type == 'text') field = this;

	$(field).focus(toggleValue).each(guessToggleFieldTitle).each(setToggleFieldClass);

	$(field).parents('form').submit(function(){$(field).focus(); $(field).unbind('blur'); });

}



function label(checkbox){



	if(checkbox.title != ''){

		var titles = checkbox.title.split('|');

		if(titles.length == 1) return;// titles[1] = titles[0];

		if($(checkbox).next().attr('tagName') == undefined){

			

			$(checkbox).parent('label').wrapInner('<span></span>');

			$(checkbox).insertBefore($(checkbox).parent());

			

		}

		$(checkbox).next().html(' '+titles[checkbox.checked*1]);

		

	}

}



function modifyCheckboxes(parent){

	if(parent == null) parent = 'body';

	$(':checkbox', $(parent)).click(function(){

		check(this);

	}).each(function(){label(this)}).filter(':checked').parent('label').addClass('checked').end();

};



function modifyRadios(){

	$(':radio').click(function(){

		$(':radio[name="'+this.name+'"]').each(function(){check(this)});

		check(this);}).each(function(){label(this)}).filter(':checked').parent('label').addClass('checked').children('.draft').parent('label').addClass('r').end();

};



function modifySelects(){

	$('option[value=""]:first','select').addClass('dis');

	$('option[value=""]:first','select:not(.nodis)').attr('disabled','disabled');

};



function modifyDigidate(){

	$(':input', '.digidate, .digidatetime').each(addToggleValue);

}

	

function modifyCaptcha(captcha){

	if(captcha == null) captcha = $('#captcha');

	captcha.click(function(){this.src = '/captcha/default/' + new Date().getSeconds() + new Date().getMilliseconds();});

}



function modifyAjaxStatus(ajaxstatus){

	if(ajaxstatus == null) ajaxstatus = $('#ajaxstatus');

	ajaxstatus.ajaxStart(function(){

		$('.mess', $(this).parents('form')).hide();

		$(this).show();

	}).ajaxStop(function(){

		$(this).hide();

	});

}

	

function clearForm(form){		

	if(form == null) form = $('form');

	$(':input', form).each(function(){

		this.focus();

		switch(this.name){

			default:

				switch(this.type){

					case 'select-one':

						var i = 0;

						var defSel = 0;

						while(i < this.options.length){

							if(this.options[i].defaultSelected){ 

								defSel = i; 

								break;

							};

							i++;

						};

						this.options[defSel].selected = 'selected';

					break;

					case 'submit':

					case 'button':

					case 'image':

					break;

					default:

						this.value = '';

					break;

				}

			break;

		};

		this.blur();

	});



}	

	

/**

 * UTILITIES

 */



/**

 * Very simple analog of sprintf function

 * @param string - template for params placement, params must have {$any_string_here} syntax.

 * @param mixed  - any quantity of params, they are substituted into the string in ascending order.

 * @return string

 *

 * Example: sprintf('<li id="foto_{$id}"><a href="{$url}"><img src="{$src}" alt="{$alt}"></a></li>', 

 *                         1, 'http://www.yandex.ru', '/img/girl.jpg', 'cool_girl') 

 * will produce <li id="foto_1"><a href="http://www.yandex.ru"><img src="/img/girl.jpg" alt="cool_girl"></a></li>

 */



function sprintf(string) {

     var regexp = /{\$([^}]+)}/;     

          

     for(var i = 1; i <= arguments.length; i++) {

          string = string.replace(regexp, arguments[i]);

     }

     

     return string;     

}



 

function strip_tags(str, allowed_tags) {

    var key = '', tag = '';

    var matches = allowed_array = [];

    var allowed_keys = {};

    

    if (allowed_tags) {

        allowed_tags  = allowed_tags.replace(/[\<\> ]+/g, '');;

        allowed_array = allowed_tags.split(',');

        

        for (key in allowed_array) {

            tag = allowed_array[key];

            allowed_keys['<' + tag + '>']   = true;

            allowed_keys['<' + tag + ' />'] = true;

            allowed_keys['</' + tag + '>']  = true;

        }

    }

 

    // Match tags

    matches = str.match(/(<\/?[^>]+>)/gi);

    

    // Is tag not in allowed list? Remove from str! 

    for (key in matches) {

        // IE7 Hack

        if (!isNaN(key)) {

            tag = matches[key].toString();

            if (!allowed_keys[tag]) {

                str = str.replace(tag, "");

            }

        }

    }

    

    return str;

}





function addBookmark(lnk, title, url) {



	if(lnk.href != null && lnk.href == '#') lnk.href = self.location;

	if(lnk.title != null && lnk.title == '') lnk.title = document.title;



	if(url == null) url = lnk.href;

	if(title == null) title = lnk.title;

			

	if (window.sidebar || (window.opera && window.print)) { // Mozilla Firefox Bookmark

		return true;

	} else if( window.external ) { // IE Favorite

		window.external.AddFavorite( url, title); 

	}

	return false;	

}



function redirectTimer(url, timeleft)

{

	if(url == null)

		if(!$('#redirecttimer').length)

			return;

		else

			timer = $('#redirecttimer');

	

	if(url == null)

		if(timer.attr('title') == '')

			return;

		else

			url = timer.attr('title');

				

	if(timeleft == null)

		if(timer.text() == '')

			timeleft = 10;

		else

			timeleft = timer.text();

		

		

	if(timeleft > 0){

	

		timer.text(timeleft);

		setTimeout(function(){redirectTimer(url, --timeleft)}, 1000);

		

	}else if(url != null)

		location.href=url;

	

}



function screenHeight(){

	

  if (self.innerWidth)

	return self.innerHeight;

  else if (document.documentElement && document.documentElement.clientWidth)

    return document.documentElement.clientHeight;

  else if (document.body)

    return document.body.clientHeight;

  

  return $().height();



}



function declension_numerals(num, first, two2four, other) {

		var tag = [];



		if(typeof num == 'object'){

			tag = num.slice(1);

			num = num[0];

		}

		

		if(first === null) return num;



		if(typeof first == 'object'){

			two2four = first[1];

			other = first[2];

			first = first[0];

		}



		if(two2four == undefined) two2four = first;

		if(other == undefined) other = first;

		

		var last = num % 10;

		var end = other;

		

		if (parseInt((num % 100) / 10) != 1) {

			switch(last){

				case 1:

					end = first;

					break;

				case 2:

				case 3:

				case 4:

					end = two2four;

					break;

				default:

					end = other;

			}

		}

		

		if(tag.length){

			num = tag[0] + num.toString();

			if(tag[1] != null) num += tag[1];

		}

		

		return num + " " + end;

	}



/**

 * DROPDOWNMENU

 */

 

var DDMtimeout = 400;

var DDMclosetimer = 0;

var DDMenuitem = 0;



function DDM_open(){

	DDM_canceltimer();

	if(!$(this).find('ul:hidden, .popup:hidden').length) return;

	DDM_close();

	DDMenuitem = $(this).find('ul:hidden, .popup:hidden');

	if($(this).width() > DDMenuitem.width()) DDMenuitem.width($(this).width());

	DDMenuitem.show();

	 

	if($('iframe',this).length){

		ul = $('iframe',this).parents('ul');

		$('iframe',this).width(ul.width());

		$('iframe',this).height(ul.height());

	};

}



function DDM_close(){  

	if(DDMenuitem) DDMenuitem.hide();

}



function DDM_timer(){

	DDMclosetimer = window.setTimeout(DDM_close, DDMtimeout);

}



function DDM_canceltimer(){

	if(DDMclosetimer){

		window.clearTimeout(DDMclosetimer);

		DDMclosetimer = null;

	}

}



//$(document).click(DDM_close);