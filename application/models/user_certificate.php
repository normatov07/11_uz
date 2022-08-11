<?php defined('SYSPATH') or die('No direct script access.');

class User_Certificate_Model extends ORM {

	protected $has_one = array('user');

    protected $_supported_doc_types = array(
        'text/plain', //txt
        'text/rtf', //rtf
        'application/pdf', //pdf
        'application/msword', //doc
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', //docx
        'application/vnd.oasis.opendocument.text', //odt
    );

    /**
     * проверяет загруженные сканы "гувохномы" и лицензии
     *
     * @param array $file - массив с данными о загруженных файлах сканов
     * @param UserModel $user - объект модели "Пользователь"
     * @param string $file_code - наименование поля в таблице, в котором
     *                            будем хранить путь к загруженному файлу
     *                            относительно корня сайта
     * @param boolean $delete_old_files - удалять ли уже имеющиеся файлы
     * @return array - массив с путями к загруженным файлам сканов
     */
    public function check_uploaded_file($file, $user, $file_code, $delete_old_files = FALSE)
    {
        $result = array();
//        $save_dir = Kohana::config('app.USER_CERT_DIR').'/'.strtr($user->email, '@', '_');
        $save_dir = Kohana::config('app.USER_CERT_DIR').'/'.$user->id;
        if (!is_dir($save_dir)) mkdir($save_dir, 0777, $recursive = true);

        if (is_uploaded_file($file['tmp_name']))
        {
            if ($file_code == 'other')
            {
                $errors = $this->_is_uploaded_doc_valid($file);
            }
            else
            {
                $errors = $this->_is_uploaded_image_valid($file);
            }
            if (!is_array($errors))
            {
                if ($delete_old_files)
                {
                    $this->remove_file($file_code);
                }
                $attachment_name = $file_code.'.'.pathinfo($file['name'], PATHINFO_EXTENSION);
//                $save_name = $save_dir.'/'.$attachment_name;
                $save_name = $save_dir.'/'.$attachment_name;
                move_uploaded_file($file['tmp_name'], $save_name);
                $result = array(
                    'path' => $save_name,
                    'name' => $attachment_name,
                    'mimetype' => $file['type'],
                );
            }
            else
            {
                $result['errors'] = $errors;
            }
        }

        return $result;
    }

    /**
     * проверяет на валидность загруженный файл
     *
     * @param array $image - массив с данными о загруженном файле
     * @return mixed - true или массив с сообщениями об ошибках
     */
    protected function _is_uploaded_image_valid($image)
    {
        $is_valid = true;
//        list($file_type, $image_type) = explode('/', $image['type']);
        // проверяем тип загруженного файла
//        $is_valid = $is_valid && ($file_type == 'image') && in_array($image_type, array('jpg', 'jpeg', 'tiff'));
//        if (!$is_valid)
//        {
//            $errors[] = 'Файл, который Вы пытаетесь загрузить не является изображением или формат этого файла не поддерживается.';
//        }

//          // проверяем размер загруженного файла
//        $is_valid = $is_valid && (($image['size'] <= (1024*1024*4)) && ($image['size'] >= (1024*100)));
//        $is_valid = $is_valid && ($image['size'] <= (1024*1024*4));
//        if (!$is_valid)
//        {
//            $errors[] = 'Файл, который Вы пытаетесь загрузить имеет недопустимый размер.';
//        }
//        // проверяем размер по ширине и высоте картинки
//        if ($file_type == 'image')
//        {
//            list($image_width, $image_height, ) = getimagesize($image['tmp_name']);
//            $is_valid = $is_valid
//                && ((600 <= $image_width) && ($image_width <= 3000))
//                && ((600 <= $image_height) && ($image_height <= 3000));
//            if (!$is_valid)
//            {
//                $errors[] = 'Изображение, которое Вы пытаетесь загрузить имеет недопустимые размеры.';
//            }
//        }

//        return ($is_valid)?$image['tmp_name']:false;
        return ($is_valid)?true:$errors;
    }

    protected function _is_uploaded_doc_valid($doc)
    {
        $is_valid = true;
        list($file_type, $doc_type) = explode('/', $doc['type']);
        // проверяем тип загруженного файла
        $is_valid = $is_valid && (in_array($doc['type'], $this->_supported_doc_types));
        if (!$is_valid)
        {
            $errors[] = 'Формат файла, который Вы пытаетесь загрузить не поддерживается.';
        }
//          // проверяем размер загруженного файла
//        $is_valid = $is_valid && (($image['size'] <= (1024*1024*4)) && ($image['size'] >= (1024*100)));
//        $is_valid = $is_valid && ($image['size'] <= (1024*1024*4));
//        if (!$is_valid)
//        {
//            $errors[] = 'Файл, который Вы пытаетесь загрузить имеет недопустимый размер.';
//        }
    }
    /**
     * функция, которая будет очищать папку с сертификатами пользователя
     * от возможного "мусора"
     *
     * @param string $field - наименование поля таблицы, в котором
     *                        хранится путь к файлу относительно корня сайта
     */
    public function remove_file($field)
    {
        if ($this->loaded)
        {
            if (is_file($_SERVER['DOCUMENT_ROOT'].$this->$field))
            {
                unlink($_SERVER['DOCUMENT_ROOT'].$this->$field);
            }
        }
    }
}
?>
