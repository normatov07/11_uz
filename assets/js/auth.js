$(function() {

	function registerHandle(json) {
		if (showErrors(json.errors)) {
			refreshCaptcha();
		} else {
			location.href='/registration_success/';
		}
	}

	function activateHandle(json) {
		if (!showErrors(json.errors)) {
			location.href='/activation_success/';
		}
	}

	function lostpassHandle(json) {
		if (showErrors(json.errors)) {
			refreshCaptcha();
		} else {
//			location.href='/lostpass_success/';
			location.href='/change_password/';
		}
	}

	function changepassHandle(json) {
		if (showErrors(json.errors)) {
			refreshCaptcha();
		} else {
			location.href='/lostpass_success/';
		}
	}

	function emailChangeHandle(json) {
		if (!showErrors(json.errors)) {
			location.href='/email_change_success/';
		}
	}

	var registerOptions = $.extend({}, ajaxOptions || {}, { success: registerHandle });
	var activateOptions = $.extend({}, ajaxOptions || {}, { success: activateHandle });
	var lostpassOptions = $.extend({}, ajaxOptions || {}, { success: lostpassHandle });
	var changepassOptions = $.extend({}, ajaxOptions || {}, { success: changepassHandle });
	var emailChangeOptions = $.extend({}, ajaxOptions || {}, { success: emailChangeHandle });

	if(useAjax){
		$('#register_form').ajaxForm(registerOptions);
		$('#activate_form').ajaxForm(activateOptions);
		$('#lostpass_form').ajaxForm(lostpassOptions);
		$('#changepass_form').ajaxForm(changepassOptions);
		$('#email_change_form').ajaxForm(emailChangeOptions);
	}
/*
	$('#register_button').click(function() {
		return $('#register_form').submit();
	});
*/
	$('#email', '#register_form').focus();
});
