<?php defined('SYSPATH') or die('No direct script access.');

class View extends View_Core {
	
	public function render($print = FALSE, $renderer = FALSE){
	
		try{
			
			return parent::render($print, $renderer);
		
		} catch (Kohana_Exception $e) {
			echo 'Ошибка вывода страницы! Попробуйте обновить страницу позже. Если ошибка повторится, пожалуйста, <a href="/contacts/">сообщите нам</a>.';
			@Lib::log($e);
		}	

	}
		
}

/* ?> */