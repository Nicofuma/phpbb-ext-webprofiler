<?php
/**
 *
 * WebProfiler extension for the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @copyright (c) Fabien Potencier <fabien@symfony.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace nicofuma\webprofiler\data_collector;

use Symfony\Component\HttpFoundation\Request as Symfony_Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * AjaxDataCollector.
 *
 * @author Bart van den Burg <bart@burgov.nl>
 */
class ajax extends DataCollector
{
	public function collect(Symfony_Request $request, Response $response, \Exception $exception = null)
	{
		// all collecting is done client side
	}

	public function getName()
	{
		return 'ajax';
	}
}
