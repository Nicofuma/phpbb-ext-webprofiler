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

/**
* Special Request DataCollector to hide the passwords.
*/
class request extends RequestDataCollector
{
	public function collect(SymfonyRequest $request, Response $response, \Exception $exception = null)
	{
		parent::collect($request, $response, $exception);

		$passwords = array();
		foreach ($this->data['request_request'] as $key => $value)
		{
			if (strpos($key, 'password') !== false)
			{
				$passwords[] = $value;
				$this->data['request_request'][$key] = '*********';
			}
		}

		$this->data['content'] = str_replace($passwords, '*********', $this->data['content']);
	}
}
