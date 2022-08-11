<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * Base MY.Engine Admin controller.
 */

class AdmController extends Controller {

	public function __construct()
	{
		parent::__construct();
		
		if(!$this->hasAccess('moderator')):
			Lib::pagenotfound();
			return;
		endif;
		
		
		// Load the menu template
		$this->admMode = $this->template->admMode = true;
		$this->addCss('adm.css');
		$this->addJs('adm.js');
		
		$this->template->titleBlock = new View('adm/b_title_view');
	}


}