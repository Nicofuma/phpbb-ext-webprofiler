<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace nicofuma\webprofiler\phpbb\db;

use Symfony\Component\Stopwatch\Stopwatch;
use phpbb\db\driver\driver_interface;

/**
* Times the time spent to execute the sql queries.
*/
class timed implements driver_interface
{
	protected $db;

	/**
	* Constructor.
	*
	* @param driver_interface $db The real database connection
	* @param Stopwatch $stopwatch A Stopwatch instance
	*/
	public function __construct(driver_interface $db, Stopwatch $stopwatch)
	{
		$this->db = $db;
		$this->stopwatch = $stopwatch;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_row_count($table_name)
	{
		return $this->db->get_row_count($table_name);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_estimated_row_count($table_name)
	{
		return $this->db->get_estimated_row_count($table_name);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_lower_text($column_name)
	{
		return $this->db->sql_lower_text($column_name);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_error($sql = '')
	{
		return $this->db->sql_error($sql);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_buffer_nested_transactions()
	{
		return $this->db->sql_buffer_nested_transactions();
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_bit_or($column_name, $bit, $compare = '')
	{
		return $this->db->sql_bit_or($column_name, $bit, $compare);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_server_info($raw = false, $use_cache = true)
	{
		return $this->db->sql_server_info($raw, $use_cache);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_return_on_error($fail = false)
	{
		return $this->db->sql_return_on_error($fail);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_build_array($query, $assoc_ary = array())
	{
		return $this->db->sql_build_array($query, $assoc_ary);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_fetchrowset($query_id = false)
	{
		return $this->db->sql_fetchrowset($query_id);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_transaction($status = 'begin')
	{
		return $this->db->sql_transaction($status);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_concatenate($expr1, $expr2)
	{
		return $this->db->sql_concatenate($expr1, $expr2);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_case($condition, $action_true, $action_false = false)
	{
		return $this->db->sql_case($condition, $action_true, $action_false);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_build_query($query, $array)
	{
		return $this->db->sql_build_query($query, $array);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_fetchfield($field, $rownum = false, $query_id = false)
	{
		return $this->db->sql_fetchfield($field, $rownum, $query_id);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_fetchrow($query_id = false)
	{
		return $this->db->sql_fetchrow($query_id);
	}

	function __get($name)
	{
		return $this->db->{$name};
	}

	/**
	* {@inheritdoc}
	*/
	public function cast_expr_to_bigint($expression)
	{
		return $this->db->cast_expr_to_bigint($expression);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_nextid()
	{
		return $this->db->sql_nextid();
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_add_num_queries($cached = false)
	{
		return $this->db->sql_add_num_queries($cached);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0)
	{
		$e = $this->stopwatch->start(sprintf('sql (%s)', $this->db->sql_layer), 'database');

		$result = $this->db->sql_query_limit($query, $total, $offset, $cache_ttl);

		$e->stop();

		return $result;
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_query($query = '', $cache_ttl = 0)
	{
		$e = $this->stopwatch->start(sprintf('sql (%s)', $this->db->sql_layer), 'database');

		$result = $this->db->sql_query($query, $cache_ttl);

		$e->stop();

		return $result;
	}

	/**
	* {@inheritdoc}
	*/
	public function cast_expr_to_string($expression)
	{
		return $this->db->cast_expr_to_string($expression);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		return $this->db->sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port, $persistency, $new_link);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_bit_and($column_name, $bit, $compare = '')
	{
		return $this->db->sql_bit_and($column_name, $bit, $compare);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_freeresult($query_id = false)
	{
		return $this->db->sql_freeresult($query_id);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_num_queries($cached = false)
	{
		return $this->db->sql_num_queries($cached);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_multi_insert($table, $sql_ary)
	{
		$e = $this->stopwatch->start(sprintf('sql (%s)', $this->db->sql_layer), 'database');

		$result = $this->db->sql_multi_insert($table, $sql_ary);

		$e->stop();

		return $result;
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_affectedrows()
	{
		return $this->db->sql_affectedrows();
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_close()
	{
		return $this->db->sql_close();
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_rowseek($rownum, &$query_id)
	{
		return $this->db->sql_rowseek($rownum, $query_id);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_escape($msg)
	{
		return $this->db->sql_escape($msg);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_like_expression($expression)
	{
		return $this->db->sql_like_expression($expression);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_report($mode, $query = '')
	{
		return $this->db->sql_report($mode, $query);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_in_set($field, $array, $negate = false, $allow_empty_set = false)
	{
		return $this->db->sql_in_set($field, $array, $negate, $allow_empty_set);
	}
}
