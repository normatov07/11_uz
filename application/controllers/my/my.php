<?php defined('SYSPATH') or die('No direct script access.');

/**
 * My controller
 */

class My_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();

		$this->template->titleBlock = new View('my/b_title_view');
	}

	public function index()
	{
		if(!$this->hasAccess('user')) return;
		
		$this->view = new View('my/index_view');
		$this->view->user = $this->user;
		
		$this->title = $this->template->titleBlock->title = "Личный кабинет";
		$this->template->titleBlock->pageid = 'index';
		
		$this->view->messageList = ORM::factory('message')->limit(3)->findIncoming($this->user->id);
		$this->view->userIDs = $this->view->messageList->getValues('user_id');
		if(count($this->view->userIDs)) $this->view->userListByID = ORM::factory('user')->in('id', $this->view->userIDs)->find_all()->as_id_array();

	}
}