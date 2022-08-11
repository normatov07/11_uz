<?php defined('SYSPATH') or die('No direct script access.');



class Category_Model extends ORM {



	protected $has_many = array('properties','offers','title_formats');

	protected $has_one = array('category_stat');



	protected $has_and_belongs_to_many = array('types');



	protected $sorting = array('left_key' => 'asc');



//	public $parentObj;

	public $old_parent_id;



	public $items = array();



	public $new_priority;

	public $old_priority;



	public $right_key_near;

	public $parent_level;



//	public $has_children;

	public $childrenIDs;



	public $type_id = array();

	public $type_autoformat = array();



	// Name of the child

	protected $children_tablename = 'categories';



	// Parent keyword name

	protected $parent_key = 'parent_id';



	public function __construct($id=NULL)

	{

		parent::__construct($id);



		if($id):

			$this->old_parent_id = !empty($this->parent_id)?$this->parent_id:NULL;

			$this->old_priority = !empty($this->priority)?$this->priority:NULL;

		endif;

	}





/**

	 * Overload ORM::__get to support "parent" and "children" properties.

	 *

	 * @param   string  column name

	 * @return  mixed

	 */

	public function __get($column)

	{

		switch((string) $column):

			case 'url':



				if (empty($this->object[$column])) {

					$this->object[$column] = '/cat/' . ($this->codename?$this->codename:$this->id).'/';

				}

				return $this->object[$column];



			break;

			case 'url_rss':



				if (empty($this->object[$column])) {

					$this->object[$column] = '/rss/' . $this->codename.'.xml';

				}

				return $this->object[$column];



			break;

			case 'stitle':



				if (empty($this->object[$column])) {

					$this->object[$column] = ($this->short_title ? $this->short_title : $this->title);

				}

				return $this->object[$column];



			break;

			case 'parent':



				if (empty($this->related[$column]))

				{

					// Load parent model

					$model = ORM::factory(inflector::singular($this->children_tablename));



					if (isset($this->object[$this->parent_key]))

					{

						// Find parent of this object

						$model->where($this->primary_key, $this->object[$this->parent_key])->find();

					}



					$this->related[$column] = $model;

				}



				return $this->related[$column];



			break;



			case 'has_children':



				if (!isset($this->related[$column])){

					$this->related[$column] = ($this->right_key - $this->left_key) > 1;

				}

				return $this->related[$column];

			break;



			case 'parents':



				if (empty($this->related[$column]))

				{

					$this->related[$column] = ORM::factory(inflector::singular($this->children_tablename))->where(array('left_key <=' => $this->left_key, 'right_key >=' => $this->right_key))->find_all();

				}



				return $this->related[$column];



			break;



			case 'children':



				if (empty($this->related[$column]))

				{

					$model = ORM::factory(inflector::singular($this->children_tablename));



					if ($this->children_tablename === $this->table_name)

					{

						// Load children within this table

						$this->related[$column] = $model

							->where($this->parent_key, $this->object[$this->primary_key])

							->find_all();

					}

					else

					{

						// Find first selection of children

						$this->related[$column] = $model

							->where($this->foreign_key(), $this->object[$this->primary_key])

							->where($this->parent_key, NULL)

							->find_all();

					}

				}



				return $this->related[$column];



			break;

			case 'siblings':



				if (empty($this->related[$column]))

				{

					$model = ORM::factory(inflector::singular($this->children_tablename));



					if ($this->children_tablename === $this->table_name)

					{

						// Load children within this table



						$this->related[$column] = $model

							->where($this->parent_key, $this->object[$this->parent_key])

							->find_all();

					}

				}



				return $this->related[$column];

			break;

			case 'title_format_by_type':

				if (empty($this->related[$column]))

				{

					$this->related[$column] = ORM::factory('title_format')->where($this->foreign_key(),$this->{$this->primary_key})->find_all()->as_id_array('type_id');

				}



				return $this->related[$column];

			break;

		endswitch;



		return parent::__get($column);

	}



    /**

     * @todo - при выключении раздела верхнего уровня,

     * надо исключать из выборки все его подразделы

     *

     * @param int $limit

     * @param int $offset

     * @return ORM_Iterator

     */

	public function find_all_enabled($limit = NULL, $offset = 0){

		$this->where('status','enabled');

		return parent::find_all($limit = NULL, $offset = 0);

	}



    public function find_all_enabled_cached() {

        $cache = Cache::instance();

        $catalog = $cache->get('main_catalog');

        if (!$catalog) {

            $catalog = $this->find_all_enabled()->as_id_array();

            $cache->set('main_catalog', $catalog, null, 18000);

        }



        return $catalog;

    }



	public function save(){



/* NEW */



		if($this->id == ''):



			$this->getRightKeyNearAndParentLevel();



			$this->left_key = $this->right_key_near + 1;

			$this->right_key = $this->right_key_near + 2;



			$this->level = $this->parent_level + 1;



			if($this->parent_id != 0):

/*

	В случае если есть родительский раздел

	Обновляем ключи существующего дерева, узлы стоящие за родительским узлом и Обновляем родительскую ветку:

	UPDATE my_tree SET right_key = right_key + 2, left_key = IF(left_key > $right_key, left_key + 2, left_key) WHERE right_key >= $right_key

*/

				$query = $this->db->query(sprintf('UPDATE %s SET right_key = right_key + 2, left_key = IF(left_key >= %d, left_key + 2, left_key), priority = IF(parent_id = %d and priority >= %d, priority + 1, priority) WHERE right_key >= %d', $this->table_name, $this->right_key_near + 1, $this->parent_id, $this->priority, $this->right_key_near + 1 ));

			else:

				$query = $this->db->query(sprintf('UPDATE %s SET right_key = right_key + 2, left_key = left_key + 2, priority = IF(parent_id = %d and priority >= %d, priority + 1, priority) WHERE left_key > %d', $this->table_name, $this->parent_id, $this->priority, $this->right_key_near));

			endif;



		else:

/* CHANGE PARENT */

			if($this->parent_id != $this->old_parent_id or $this->new_priority != $this->old_priority):



				$this->getRightKeyNearAndParentLevel();



//	1. Ключи и уровень перемещаемого узла;

				$level = $this->level;

				$left_key = $this->left_key;

				$right_key = $this->right_key;



//	2. Уровень нового родительского узла (если узел перемещается в "корень" то сразу можно подставить значение 0):

				if($this->parent_id == 0):

					$level_up = 0;

				else:

//					if(empty($this->parentObj)) $this->getParent();

					$level_up = $this->parent->level;

				endif;



/* 3. Правый ключ узла за который мы вставляем узел (ветку):*/



				$right_key_near = $this->right_key_near;



/* 4. Определяем смещения: */

				$skew_level = $level_up - $level + 1; // - смещение уровня изменяемого узла;

				$skew_tree = $right_key - $left_key + 1; // - смещение ключей дерева;



/*				$query = $this->db->query(sprintf('SELECT id FROM %s WHERE left_key >= %d AND right_key <= %d', $this->table_name, $left_key, $right_key));



				$id_edit = array();

				foreach($query->result() as $row):

					$id_edit[] = $row->id;

				endforeach;



				$id_edit = join(',',$id_edit); */



/* 5. Так же требуется определить: в какую область перемещается узел, для этого сравниваем

$right_key и $right_key_near, если $right_key_near больше, то узел перемещается в облась вышестоящих узлов,

иначе - нижестоящих (почему существует разделение описано ниже).

*/

				$priorityFix = '';



				if($this->parent_id != $this->old_parent_id):

					$priorityFix = sprintf(', priority = IF(parent_id = %d and priority > %d, priority - 1, IF(parent_id = %d and priority >= %d, priority + 1, priority))', $this->old_parent_id, $this->old_priority, $this->parent_id, $this->priority);

				elseif($this->priority < $this->old_priority):

					$priorityFix = sprintf(', priority = IF(parent_id = %d and priority < %d and priority >= %d, priority + 1, priority)', $this->parent_id, $this->old_priority, $this->priority);

				elseif($this->priority < $this->old_priority):

					$priorityFix = sprintf(', priority = IF(parent_id = %d and priority > %d and priority <= %d, priority - 1, priority)', $this->parent_id, $this->old_priority, $this->priority);

				endif;





				if($right_key_near < $right_key):

// При перемещении вверх по дереву

//Определяем смещение ключей редактируемого узла

					$skew_edit = $right_key_near - $left_key + 1;



/* UPDATE my_table

SET right_key = IF(left_key >= $left_key, right_key + $skew_edit, IF(right_key < $left_key, right_key + $skew_tree, right_key)),

level = IF(left_key >= $left_key, level + $skew_level, level),

left_key = IF(left_key >= $left_key, left_key + $skew_edit, IF(left_key > $right_key_near, left_key + $skew_tree, left_key))

WHERE right_key > $right_key_near AND left_key < $right_key					 */







					$this->db->query(

//echo (

					"UPDATE ". $this->table_name."

					SET right_key = IF(left_key >= $left_key, right_key + $skew_edit, IF(right_key < $left_key, right_key + $skew_tree, right_key)),

					level = IF(left_key >= $left_key, level + $skew_level, level),

					left_key = IF(left_key >= $left_key, left_key + $skew_edit, IF(left_key > $right_key_near, left_key + $skew_tree, left_key)) ".

					$priorityFix .

					" WHERE right_key > $right_key_near AND left_key < $right_key");



				else:

// При перемещении вниз по дереву

// Определяем смещение ключей редактируемого узла

					$skew_edit = $right_key_near - $left_key + 1 - $skew_tree;



/* UPDATE my_table

SET left_key = IF(right_key <= $right_key, left_key + $skew_edit, IF(left_key > $right_key, left_key - $skew_tree, left_key)),

level = IF(right_key <= $right_key, level + $skew_level, level),

right_key = IF(right_key <= $right_key, right_key + $skew_edit, IF(right_key <= $right_key_near, right_key - $skew_tree, right_key))

WHERE right_key > $left_key AND left_key <= $right_key_near */



					$this->db->query(

//echo (

					"UPDATE ". $this->table_name."

					SET left_key = IF(right_key <= $right_key, left_key + $skew_edit, IF(left_key > $right_key, left_key - $skew_tree, left_key)),

					level = IF(right_key <= $right_key, level + $skew_level, level),

					right_key = IF(right_key <= $right_key, right_key + $skew_edit, IF(right_key <= $right_key_near, right_key - $skew_tree, right_key)) ".

					$priorityFix .

					" WHERE right_key > $left_key AND left_key <= $right_key_near"

					);

//exit;

				endif;





			endif;



		endif;

//		$this->priority = $this->new_priority;

		parent::save();

	}





	public function save_with_related(){



		$typeIDs[] = array();

		foreach($this->types as $type):

			if(is_array($this->type_id) and in_array($type->id, $this->type_id)):

				$typeIDs[] = $type->id;

			else:

				$this->remove($type);

			endif;

		endforeach;



		$i = 0;

		while(isset($this->type_id[$i])):

			if(!in_array($this->type_id[$i], $typeIDs)):

				$type = new Type_Model($this->type_id[$i]);

				$this->add($type);

			endif;

			$i++;

		endwhile;



		$this->save();



		$current_type_format = $this->title_format_by_type;

		if(!empty($this->type_autoformat) and count($this->type_autoformat)):

			foreach($this->type_autoformat as $type_id => $format):

				if(empty($current_type_format[$type_id])):

					$current_type_format[$type_id] = new Title_Format_Model();

					$current_type_format[$type_id]->type_id = $type_id;

					$current_type_format[$type_id]->{$this->foreign_key()} = $this->{$this->primary_key};

				endif;

				$current_type_format[$type_id]->format = $format;

				$current_type_format[$type_id]->save();

			endforeach;

		endif;



		$i = 0;



		$saved_properties = array();



		while(isset($this->items[$i])):



			$item = ORM::factory('property', !empty($this->items[$i]->id)?$this->items[$i]->id:NULL);



			$item->title = $this->items[$i]->title;

			$item->codename = $this->items[$i]->codename;

			$item->datatype = $this->items[$i]->datatype;

			$item->units = $this->items[$i]->units;

			$item->minlength = $this->items[$i]->minlength;

			$item->maxlength = $this->items[$i]->maxlength;

			$item->required = @$this->items[$i]->required;

			$item->list_id = @$this->items[$i]->list_id;

			$item->isquicklist = @$this->items[$i]->isquicklist;

			$item->priority = $i;



			$item->{$this->foreign_key()} = $this->id;



			$item->save();



			$saved_properties[] = $item->id;



			$i++;



		endwhile;



		$i = 0;



		while(isset($this->properties[$i])):

			if(!in_array($this->properties[$i]->id, $saved_properties)) $this->properties[$i]->delete();

			$i++;

		endwhile;







		return true;

	}



	public function delete($id = 1, $except = 1){



		// Delete all belongings for this object



		if ( count($this->has_many) or count($this->has_one) or count($this->has_and_belongs_to_many)){



			$this->delete_belongings();

			$sublist = $this->getFullSubList();

			foreach($sublist as $item):

				$item->delete_belongings();

			endforeach;



		}



//		Удаляем узел (ветку): DELETE FROM my_tree WHERE left_key >= $left_key AND right_ key <= $right_key

		$query = sprintf('DELETE FROM %s WHERE left_key >= %d AND right_key <= %d', $this->table_name, $this->left_key, $this->right_key);

		$res = $this->db->query($query)->count();



//		Обновление родительской ветки и Обновление последующих узлов

//		UPDATE my_tree SET left_key = IF(left_key > $left_key, left_key - ($right_key - $left_key + 1), left_key), right_key = right_key - ($right_key - $left_key + 1) WHERE right_key > $right_key

		$query = sprintf('UPDATE %s SET

		left_key = IF(left_key > %d, left_key - %d, left_key),

		right_key = right_key - %d'.

		', priority = IF(parent_id = %d and priority > %d, priority - 1, priority)'.

		' WHERE right_key > %d',



		$this->table_name,

		$this->left_key, ($this->right_key - $this->left_key + 1),

		($this->right_key - $this->left_key + 1),

		$this->parent_id, $this->priority,

		$this->right_key);

		$this->db->query($query);

exit;



		$this->clear();



		return $res;



	}





	public function getRightKeyNearAndParentLevel(){





		if($this->new_priority == NULL):



			$res = $this->db->select('right_key, level, priority')->from($this->table_name)->where('parent_id', $this->parent_id)->limit(1)->orderby('left_key','desc')->get()->current();

			if($res):

				$this->right_key_near = $res->right_key;

				$this->parent_level = $res->level?$res->level - 1:$res->level;

				$this->priority = $res->priority+1;

			else:

				if($this->parent_id == 0):

					$this->right_key_near = 0;

					$this->parent_level = 0;

				else:

//					$this->getParent();

					$this->right_key_near = $this->parent->right_key - 1;

					$this->parent_level = $this->parent->level;

				endif;

				$this->priority = 0;

			endif;



		elseif($this->new_priority == 0):



			if($this->parent_id == 0):

				$this->right_key_near = 0;

				$this->parent_level = 0;

			else:

				$res = $this->db->from($this->table_name)->where('id', $this->parent_id)->get()->current();

				if(!$res):

					$this->new_priority = NULL;

					return $this->getRightKeyNearAndParentLevel();

				else:

					$this->right_key_near = $res->left_key;

					$this->parent_level = $res->level;

				endif;

			endif;



			$this->priority = 0;



		elseif($this->new_priority > 0):



			$res = $this->db->from($this->table_name)->where('parent_id', $this->parent_id)->orderby('left_key','asc')->limit(1,$this->new_priority - 1)->get()->current();

			if(!$res):

				$this->new_priority = NULL;

				return $this->getRightKeyNearAndParentLevel();

			else:

				$this->right_key_near = $res->right_key;

				$this->parent_level = $res->level - 1;

			endif;

			$this->priority = $this->new_priority;

		endif;



//		echo $this->right_key_near . ':' .$this->parent_level;



		return false;

	}





	public function getSubList($parent_id = NULL){

		if($parent_id == NULL)

			if(@$this->id != '') $parent_id = $this->id;

			else $parent_id = 0;

		$this->select('id, title, short_title, right_key, left_key, has_subway, has_district');

		$this->where('parent_id', $parent_id);

		return $this->find_all();

	}



	public function getFullSubList(){

		$this->select('id, title, codename, left_key, right_key, has_subway, has_district');



		$this->where(array('left_key >' => $this->left_key, 'right_key <' => $this->right_key));



		return $this->find_all();

	}





	public function countOnLevel($level){

		return $this->db->select('count(*) as amount')->from($this->table_name)->where('level', $level)->get()->current()->amount;

	}



	public function getFilters(){

		return ORM::factory('property')->where(array($this->foreign_key() => $this->id, 'list_id !=' => ''))->orderby('isquicklist','desc')->orderby('priority','asc')->find_all()->as_id_array();

	}



	public function getChildrenIDs($including_self = false){



		if(!isset($this->childrenIDs)):



			$subs = $this->getFullSubList();



			$this->childrenIDs = array();

			foreach($subs as $sub):

				$this->childrenIDs[] = $sub->id;

			endforeach;



		endif;



		if($including_self)

			return $this->childrenIDs + array($this->id);

		else

			return $this->childrenIDs;

	}



	public function getOffersCount($full = false){



		if($full and $this->id != 0):



			$ids = $this->getChildrenIDs(true);



			return ORM::factory('offer')->setFilters()->count_all_In_Category($ids);



		else:

			return ORM::factory('offer')->setFilters()->count_all_In_Category($this->id);

		endif;



	}



    /**

     * проверяет наличие ограничения на кол-во объявлений в категории

     * возвращает массив c:

     * - наименованием категории,

     * - статусом превышения лимита,

     * - максимально допустимым кол-вом объявлений

     *

     * @param User_Model $user

     * @return array

     */

    public function check_offers_limit($user)

    {

        $result = array(

            'warning_status' => false,

            'category_name' => $this->short_title,

        );



        $offers_limit_config = Lib::config('offers_limit');

        $categories_with_limit = array_keys($offers_limit_config);

        // делаем проверку на лимит кол-ва объявлений

        // для пользователей не являющихся риелторами

        // в разделе Недвижимость

        if (!empty($this)

                && in_array($this->codename, $categories_with_limit))

        {

            $offers_limit = $offers_limit_config[$this->codename];

			/*
            if (!$user->is_realtor)

            {
*/
                $offer_model = ORM::factory('offer');



                $filters = array(

                    'category_id' => $this->id,

                );

                $offers_cnt = $offer_model

                    ->setFilters($filters)

                    ->count_all_by_user($user->id, $status = 'enabled', $is_agent = FALSE);



                if ($offers_cnt >= $offers_limit)

                {

                    $result['warning_status'] = true;

                }

                $result['offers_limit'] = $offers_limit;

            }
/*
        }*/



        return $result;

    }

}

/* ?> */

