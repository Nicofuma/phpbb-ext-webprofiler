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

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\RequestDataCollector;

class request extends RequestDataCollector
{
	public function collect(SymfonyRequest $request, Response $response, \Exception $exception = null)
	{
		parent::collect($request, $response, $exception);

		if (isset($this->data['request_request']['password']))
		{
			$this->data['request_request']['password'] = '*********';
		}
	}

}
