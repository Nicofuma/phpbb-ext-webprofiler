<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @copyright (c) Fabien Potencier <fabien@symfony.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace nicofuma\webprofiler\controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\Matcher\TraceableUrlMatcher;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
* Router Controller.
*
* @author Fabien Potencier <fabien@symfony.com>
* @author Tristan Darricau <tristan@darricau.eu>
*/
class router
{
	private $helper;
	private $profiler;
	private $template;
	private $matcher;
	private $routes;


	/**
	* Constructor
	*
	* @param \phpbb\template\template $template Template object
	* @param \phpbb\controller\helper $helper   Controller helper object
	* @param \phpbb\controller\provider $provider The controller provider
	* @param UrlMatcherInterface $matcher The url matcher
	* @param Profiler $profiler The profiler object if  it exists (null otherwise)
	*/
	public function __construct(\phpbb\template\template $template, \phpbb\controller\helper $helper, \phpbb\controller\provider $provider, UrlMatcherInterface $matcher = null, Profiler $profiler = null)
	{
		$this->helper = $helper;
		$this->profiler = $profiler;
		$this->template = $template;
		$this->matcher = $matcher;
		$this->routes = $provider->get_routes();
	}

	/**
	* Renders the profiler panel for the given token.
	*
	* @param string $token The profiler token
	*
	* @return Response A Response instance
	*
	* @throws NotFoundHttpException
	*/
	public function panelAction($token)
	{
		if (null === $this->profiler) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();

		if (null === $this->matcher || null === $this->routes) {
			return new Response('The Router is not enabled.', 200, array('Content-Type' => 'text/html'));
		}

		$profile = $this->profiler->loadProfile($token);

		$context = $this->matcher->getContext();
		$context->setMethod($profile->getMethod());
		$matcher = new TraceableUrlMatcher($this->routes, $context);

		$request = $profile->getCollector('request');

		$this->assign_layout_vars($request, $profile, $token);

		$this->template->assign_vars(array(
			'token' => $token,
			'profile' => $profile,
			'collector' => $profile->getCollector('router'),
			'panel' => 'router',
			'page' => 'home',
			'request' => $request,
			'templates' => $this->get_modules($token, $profile),
			'is_ajax' => false,
			'router' => $profile->getCollector('router'),
			'traces' => $matcher->getTraces($request->getPathInfo()),
		));

		$this->template->set_filenames(array(
			'body'	=> "@nicofuma_webprofiler/collector/router_content.html",
		));

		return new Response($this->template->assign_display('body'), 200, array('Content-Type' => 'text/html'));
	}

	protected function assign_layout_vars($request, $profile, $token)
	{
		$this->assign_admin_vars($request, $profile, $token);

		$this->template->assign_vars(array(
			'search_link' 	=> $this->helper->route('_profiler_search', array('limit' => 10), false),
			'php_info_link' 	=> $this->helper->route('_profiler_phpinfo'),
			'search_action_link' 	=> $this->helper->route('_profiler_search', array(), false),
			'search_bar_path' => $this->helper->route('_profiler_search_bar'),
			'position' => 'normal',
			'profiler_url' => $this->helper->route('_profiler', array('token' => $token))
		));
	}

	protected function assign_admin_vars($request, $profile, $token)
	{
		$this->template->assign_vars(array(
			'admin_action' 	=> $this->helper->route('_profiler_import'),
			'purge_link' 	=> $this->helper->route('_profiler_purge', array('token' => $token)),
			'export_link' 	=> $this->helper->route('_profiler_export', array('token' => $token)),
		));
	}

	protected function get_modules($token, $profile)
	{
		$modules = array();
		foreach ($profile->getCollectors() as $collector_name => $collector)
		{
			$modules[$collector_name] = $this->helper->route('_profiler_panel', array('token' => $token, 'panel' => $collector_name));
		}

		return $modules;
	}
}
