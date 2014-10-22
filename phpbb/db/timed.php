<?php
/**
 *
 * WebProfiler extension for the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace nicofuma\webprofiler\phpbb\db;

use \Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use phpbb\db\driver\factory;

/**
* Times the time spent to execute the sql queries.
*/
class timed extends factory
{
	protected $stopwatch;
	protected $report = array();

	/**
	* Constructor.
	*
	* @param ContainerInterface $container The container
	* @param Stopwatch $stopwatch A Stopwatch instance
	*/
	public function __construct(ContainerInterface $container, Stopwatch $stopwatch)
	{
		parent::__construct($container);

		$this->stopwatch = $stopwatch;
	}
	/**
	* {@inheritdoc}
	*/
	public function sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0)
	{
		$e = $this->stopwatch->start($query, 'database');

		$result = parent::sql_query_limit($query, $total, $offset, $cache_ttl);

		$e->stop();

		return $result;
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_query($query = '', $cache_ttl = 0)
	{
		$e = $this->stopwatch->start($query, 'database');

		$result = parent::sql_query($query, $cache_ttl);

		$e->stop();

		return $result;
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_multi_insert($table, $sql_ary)
	{
		$e = $this->stopwatch->start(sprintf('multi-insert (%s)', $table), 'database');

		$result = parent::sql_multi_insert($table, $sql_ary);

		$e->stop();

		return $result;
	}
}
