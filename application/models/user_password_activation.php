<?php defined('SYSPATH') or die('No direct script access.');

class User_Password_Activation_Model extends ORM {

	protected $belongs_to = array('user');

	private $conf;

	public function __construct($id=NULL)
	{
		parent::__construct($id);

		$this->conf = Lib::config('auth');
		if(empty($id)) $this->generate_activation_key($this->conf['activation_key_length']);

        $this->garbage_collect();

	}


	private function generate_activation_key($len = 30) {
		$this->activation_key = text::random('alnum', $len);
	}

    private function garbage_collect()
    {
        $n = mt_rand(1, 1000);
        if ($n > 990)
        {
            $activations = $this->where('dt<', strtotime('-24 hours'))->find_all()->as_array();

            $ids = array_map(function($activation){
                return $activation['id'];
            }, $activations);

            $ids = array_values($ids);

            $this->delete_all($ids);
        }
    }
}
/* ?> */