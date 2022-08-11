<?php defined('SYSPATH') or die('No direct script access.');

	//$css_files[] = Lib::config('app.CSS_DIR').'/main-'.Lib::config('media.css','main.css').'.css';
	$css_files[] = Lib::config('app.CSS_DIR').'/main_new.css';
	$css_files[] = Lib::config('app.CSS_DIR').'/errors.css';
	

	if(!empty($code) and $code == E_PAGE_NOT_FOUND and ($auth = @Auth::instance())):
	
		$user = @$auth->authorize();
		
		if(!empty($user)):
			$js_files[] = Lib::config('app.JS_DIR').'/jquery.min.js';
			//$js_files[] = Lib::config('app.JS_DIR').'/main-'.Lib::config('media.js','main.js').'.js';
			$js_files[] = Lib::config('app.JS_DIR').'/main.js';
			$js_files[] = Lib::config('app.JS_DIR').'/jquery.form.js';
			//$js_files[] = Lib::config('app.JS_DIR').'/login-'.Lib::config('media.js','login.js').'.js';
			$js_files[] = Lib::config('app.JS_DIR').'/login.js';
		endif;
	
	else:
		$hideUserRelated = true;				
	endif;

	$title = html::specialchars($error);
	
		$content = '

<div class="main">
	<p>'. html::specialchars($description). '</p>';
	
	if(!empty($code) and $code == E_PAGE_NOT_FOUND):
	
		$content .= '<p><a href="/">Перейти на главную страницу →</a></p>';
	endif;

	if(!IN_PRODUCTION or (!empty($user) and $user->is_moderator) or !empty($_GET['superadmin'])):
	
		$content .= '
	<div class="errorpage">';
		
		if (!empty($line) AND ! empty($file)):
			$content .= '<p>' . Kohana::lang('core.error_file_line', $file, $line) . '</p>';
		endif;
		
		$content .= '<p><code class="block">' . $message . '</code></p>';
		
		if (!empty($trace)):
			$content .= '<h3>' . Kohana::lang('core.stack_trace') . '</h3>';
			$content .= $trace;
		endif;
		
		$content .= '<p class="stats">' . Kohana::lang('core.stats_footer') . '</p>';
	
		$content .= '
	</div>';

	endif;
	$content .= '
</div>';

	require_once(APPPATH . 'views/template_view.php');