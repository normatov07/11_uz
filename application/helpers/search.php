<?php
require ( "sphinxapi.php" );
/**
 * helper для поиска разной информации через sphinx
 **/
 
 class search {

	protected $sphinx;

	public function __construct() {
	 
		if (!$this->sphinx) {

			$this->sphinx = new SphinxClient();
			$this->sphinx->SetServer('127.0.0.1', 9312);
			$this->sphinx->SetMatchMode(1);
 			//$this->sphinx->SetSortMode(SPH_SORT_RELEVANCE);
		}
	}

	/*
	 * метод для общего поиска на сайте по фразе
	 *
	 * @param string строка поиска
	 * @param array массив вида array(параметр=>значение) для доп. фильтрации результатов поиска
	 * @param string строка задающая порядок сортировок
	 * @param int количество записей, которое надо найти и вернуть как результат поиска
	 * @param int смещение
	 * @return array массив ID найденных записей
	 */
	public function search($search_value, $filters, $order_by = NULL, $count, $offset = 1) {

		$this->sphinx->ResetFilters();
		$sort_order = '@relevance DESC, @id DESC';

		if ($order_by != NULL) {
			$sort_order .= ', '.$order_by.' DESC';
		}
		else {
			$this->sphinx->SetIndexWeights(array('offersTitleIndexAli'=>100, 'offersDescriptionIndexAli'=>1, 'offersParamsIndexAli'=>10));
		}

		foreach ($filters as $name=>$value):
			if (empty($value)) continue;
			switch ($name):
				case 'period':
					$this->sphinx->SetFilterRange($name, strtotime('-'.$value.' days'), time());
				break;
				default:
					if (!is_array($value)) $value = array($value);
					$this->sphinx->SetFilter($name, $value);
			endswitch;
		endforeach;

		$this->sphinx->SetSortMode(4, $sort_order);



		$this->sphinx->SetLimits($offset, $count, 1000);


		$results = array();
		try {
			//$results = $this->sphinx->Query('*'.$search_value.'*');
			$results = $this->sphinx->Query('*'.$search_value.'*', 'offersDescriptionIndexAli, offersParamsIndexAli, offersTitleIndexAli');
		}
		catch (Exception $e) {
			@Lib::log(__FILE__.'('.((int)__LINE__-3).'): '.$e->getMessage());
		}

		return $results;
	}

}
?>
