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

namespace nicofuma\webprofiler\phpbb\profiler;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Profiler\Profiler as symfony_profiler;

/**
* Extends the Symfony HttpKernel Profiler to use phpBB events with legacy front-end.
*/
class profiler_listener extends \Symfony\Component\HttpKernel\EventListener\ProfilerListener
{
	protected $request;
	protected $dispatcher;
	protected $http_kernel;
	protected $phpbb_root_path;
	protected $phpEx;

	public function __construct($request, $dispatcher, $http_kernel, $phpbb_root_path, $phpEx, symfony_profiler $profiler, RequestMatcherInterface $matcher = null, $onlyException = false, $onlyMasterRequests = false)
	{
		$this->request = $request;
		$this->dispatcher = $dispatcher;
		$this->http_kernel = $http_kernel;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;

		parent::__construct($profiler, $matcher, $onlyException, $onlyMasterRequests);
	}

	public function on_kernel_request()
	{
		if (substr($GLOBALS['request']->server('SCRIPT_NAME'), -7) === 'app.php')
		{
			return;
		}

		if (! function_exists('phpbb_get_url_matcher'))
		{
			require($this->phpbb_root_path . 'includes/functions_url_matcher.' . $this->phpEx);
		}

		try {
			$this->dispatcher->dispatch(\Symfony\Component\HttpKernel\KernelEvents::REQUEST,
				new \Symfony\Component\HttpKernel\Event\GetResponseEvent(
					$this->http_kernel,
					$this->request,
					\Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST
				));
		}
		catch (\Exception $e)
		{

		}
	}

	public function on_kernel_response()
	{
		if (substr($GLOBALS['request']->server('SCRIPT_NAME'), -7) === 'app.php')
		{
			return;
		}

		try {
			$response = new \Symfony\Component\HttpFoundation\Response('<html><body></body></html>');
			$this->dispatcher->dispatch(\Symfony\Component\HttpKernel\KernelEvents::RESPONSE,
				new \Symfony\Component\HttpKernel\Event\FilterResponseEvent(
					$this->http_kernel,
					$this->request,
					\Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST,
					$response
				));

			if ($this->request->getMethod() === 'GET')
			{
				echo $response->getContent();
			}
		}
		catch (\Exception $e)
		{
		}
	}

	public function stop_propagation(GetResponseEvent $event)
	{
		if (substr($GLOBALS['request']->server('SCRIPT_NAME'), -7) !== 'app.php')
		{
			$event->stopPropagation();
		}
	}

	public static function getSubscribedEvents()
	{
		return array_merge(parent::getSubscribedEvents(), array (
			\Symfony\Component\HttpKernel\KernelEvents::REQUEST => array('stop_propagation', 1),
			'core.common' => array('on_kernel_request', 1000),
			'core.garbage_collection' => array('on_kernel_response', 1000),
		));
	}
}
