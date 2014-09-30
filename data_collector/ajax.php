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
