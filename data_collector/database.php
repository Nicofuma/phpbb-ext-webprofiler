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

namespace nicofuma\webprofiler\data_collector;

use Symfony\Component\HttpFoundation\Request as Symfony_Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class database extends DataCollector
{
	/**
	* Database connection
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

	/**
	* @var \phpbb\request\request_interface
	*/
	protected $request;

	/**
	* @param \phpbb\db\driver\driver_interface	$db			Database connection
	* @param \phpbb\request\request_interface	$request	Request object
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\request\request_interface $request)
	{
		$this->db = $db;
		$this->request = $request;
	}

	/**
	* Collects data for the given Request and Response.
	*
	* @param Symfony_Request	$request	A Request instance
	* @param Response			$response	A Response instance
	* @param \Exception			$exception	An Exception instance
	*
	* @api
	*/
	public function collect(Symfony_Request $request, Response $response, \Exception $exception = null)
	{
		$this->data = array();
		$this->data['report'] = 'Not available yet.';
		$this->data['num_queries'] = $this->db->sql_num_queries();
		$this->data['num_cached_queries'] = $this->db->sql_num_queries(true);
		$this->data['time'] = $this->db->get_sql_time();
	}

	public function getReport()
	{
		return $this->data['report'];
	}

	public function getNum_queries()
	{
		return $this->data['num_queries'];
	}

	public function getNum_cached_queries()
	{
		return $this->data['num_cached_queries'];
	}

	public function getTime()
	{
		return $this->data['time'];
	}

	/**
	* Returns the name of the collector.
	*
	* @return string The collector name
	*
	* @api
	*/
	public function getName()
	{
		return 'database';
	}

}
