<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Administration
 * Bonus Controller.
 */
class Bonus_Controller extends AdmController {
	
	public function __construct(){
	
		parent::__construct();
		$this->view = new View('adm/bonus_view');
		$this->parent_title = 'Баннеры';
		$this->title = 'Добавление баннера';
		$this->addJs('adm_user.js');
		$this->addJs('adm_bonus.js');

	}
	
	
	public function index($id = NULL){

        $uploaddir = 'assets/img/slide/';

        $this->banners = [];

        if($_POST['task'] == "upload"){


            $url = $_POST['url'];
            $url = trim( $url);
            $url = empty( $url) ? time() : 'u_'.base64_encode(trim($url));

            $ext = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);

            $uploadfile = $uploaddir . $url.'.'.$ext;

            if (move_uploaded_file($_FILES['banner']['tmp_name'], $uploadfile)) {
                header("location: /adm/bonus");
                exit;
            } else {
                header("location: /adm/bonus");
                exit;
            }

        }

        else if($_POST['task'] == "delete"){
            $photo = $_POST['photo'];
            if(!empty($photo)){

                $uploadfile = $uploaddir . $photo;

                unlink($uploadfile);
                header("location: /adm/bonus");
                exit;

            }
        }

        else {
            if ($handle = opendir($uploaddir)) {
                while (false !== ($entry = readdir($handle))) {
                    $src = substr($entry, strrpos($entry, '.')+1);

                    if($src == "png" || $src == "jpg" || $src == "jpeg"){
                        array_push($this->banners, $entry);
                    }
                }
                closedir($handle);
            }
        }
	
	}
	
}
/* ?> */