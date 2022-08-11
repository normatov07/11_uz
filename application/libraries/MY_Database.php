<?php defined('SYSPATH') or die('No direct script access.');

class Database extends Database_Core {


	public function __construct($config = array())
	{
		parent::__construct($config);
		
	}

	public function select($sql = '*', $noescape = false)
//	public function select($sql = '*')
	{
		if (func_num_args() > 1)
		{
			$sql = func_get_args();
		}
		elseif (is_string($sql))
		{
			$sql = explode(',', $sql);
		}
		else
		{
			$sql = (array) $sql;
		}

		foreach ($sql as $val)
		{
			if (($val = trim($val)) === '') continue;

			if (strpos($val, '(') === FALSE AND $val !== '*')
			{
				if (preg_match('/^DISTINCT\s++(.+)$/i', $val, $matches))
				{
					// Only prepend with table prefix if table name is specified
					$val = (strpos($matches[1], '.') !== FALSE) ? $this->config['table_prefix'].$matches[1] : $matches[1];

					$this->distinct = TRUE;
				}
				else
				{
					$val = (strpos($val, '.') !== FALSE) ? $this->config['table_prefix'].$val : $val;
				}
/**
 * became:	$noescape added
 */
				if(empty($noescape)) $val = $this->driver->escape_column($val);
			}

			$this->select[] = $val;
		}

		return $this;
	}
	
	
	public function set($key, $value = '', $donotescape = FALSE)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		foreach ($key as $k => $v)
		{
			// Add a table prefix if the column includes the table.
			if (strpos($k, '.'))
				$k = $this->config['table_prefix'].$k;
		
			$this->set[$k] = $donotescape ? $v : $this->driver->escape($v);
		}

		return $this;
	}
	
	public function orderby($orderby, $direction = NULL)
	{
		if ( ! is_array($orderby))
		{
			$orderby = array($orderby => $direction);
		}

		foreach ($orderby as $column => $direction)
		{
			$direction = strtoupper(trim($direction));

			// Add a direction if the provided one isn't valid
			if ( ! in_array($direction, array('ASC', 'DESC', 'RAND()', 'RANDOM()', 'NULL')))
			{
				$direction = 'ASC';
			}
			
			if(preg_match('/[^\W\.]/',$column)):
				$this->orderby[] = $column .' '.$direction;
			else:
				// Add the table prefix if a table.column was passed
				if (strpos($column, '.'))
				{
					$column = $this->config['table_prefix'].$column;
				}
				$this->orderby[] = $this->driver->escape_column($column).' '.$direction;
			endif;
		}

		return $this;
	}
	

	public function join($table, $key, $value = NULL, $type = '')
	{
		$join = array();

		if ( ! empty($type))
		{
			$type = strtoupper(trim($type));

			if ( ! in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'), TRUE))
			{
				$type = '';
			}
			else
			{
				$type .= ' ';
			}
		}

		$cond = array();
		$keys  = is_array($key) ? $key : array($key => $value);
		foreach ($keys as $key => $value)
		{
			$key    = (strpos($key, '.') !== FALSE) ? $this->config['table_prefix'].$key : $key;

/**
 * was:		if (is_string($value))
 * became:	if (is_string($value) and strpos($value, '\'') === FALSE)
 */

			if (is_string($value) and strpos($value, '\'') === FALSE)
			{
				// Only escape if it's a string
				$value = $this->driver->escape_column($this->config['table_prefix'].$value);
			}

			$cond[] = $this->driver->where($key, $value, 'AND ', count($cond), FALSE);
		}

		if ( ! is_array($this->join))
		{
			$this->join = array();
		}

		if ( ! is_array($table))
		{
			$table = array($table);
		}

		foreach ($table as $t)
		{
			if (is_string($t))
			{
				// TODO: Temporary solution, this should be moved to database driver (AS is checked for twice)
				if (stripos($t, ' AS ') !== FALSE)
				{
					$t = str_ireplace(' AS ', ' AS ', $t);

					list($table, $alias) = explode(' AS ', $t);

					// Attach prefix to both sides of the AS
					$t = $this->config['table_prefix'].$table.' AS '.$this->config['table_prefix'].$alias;
				}
				else
				{
					$t = $this->config['table_prefix'].$t;
				}
			}

			$join['tables'][] = $this->driver->escape_column($t);
		}

		$join['conditions'] = '('.trim(implode(' ', $cond)).')';
		$join['type'] = $type;

		$this->join[] = $join;

		return $this;
	}
	
	
}

/* ?> */