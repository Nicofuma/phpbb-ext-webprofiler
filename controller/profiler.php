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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
* ProfilerController.
*
* @author Fabien Potencier <fabien@symfony.com>
* @author Tristan Darricau <tristan@darricau.eu>
*/
class profiler
{
	private $helper;
	private $template;
	private $profiler;

	/**
	* Constructor
	*
	* @param \phpbb\template\template $template Template object
	* @param \phpbb\controller\helper $helper   Controller helper object
	* @param \nicofuma\webprofiler\phpbb\profiler\Profiler $profiler The profiler object if  it exists (null otherwise)
	*/
	public function __construct(\phpbb\template\template $template, \phpbb\controller\helper $helper, \nicofuma\webprofiler\phpbb\profiler\Profiler $profiler = null)
	{
		$this->helper = $helper;
		$this->template = $template;
		$this->profiler = $profiler;
	}

	/**
	* Redirects to the last profiles.
	*
	* @return RedirectResponse A RedirectResponse instance
	*
	* @throws NotFoundHttpException
	*/
	public function homeAction()
	{
		if ($this->profiler === null) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();

		return new RedirectResponse($this->helper->route('_profiler_search_results', array('token' => 'empty', 'limit' => 10)), 302, array('Content-Type' => 'text/html'));
	}

	/**
	* Renders a profiler panel for the given token.
	*
	* @param Request $request The current HTTP request
	* @param string $token The profiler token
	*
	* @return Response A Response instance
	*
	* @throws NotFoundHttpException
	*/
	public function panelAction(Request $request, $token, $panel = null)
	{
		if ($this->profiler === null) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();

		$profile = $this->profiler->loadProfile($token);
		if (!$profile) {
			$this->template->assign_vars(array('about' => 'no_token', 'token' => $token));
			return new Response($this->template->assign_display('@nicofuma_webprofiler/profiler/info.html'), 200, array('Content-Type' => 'text/html'));
		}

		if ($panel == null)
		{
			$panel = $request->query->get('panel', 'request');
		}
		$page = $request->query->get('page', 'home');

		if (!$profile->hasCollector($panel)) {
			throw new NotFoundHttpException(sprintf('Panel "%s" is not available for token "%s".', $panel, $token));
		}

		$this->assign_layout_vars($request, $profile, $token);

		$this->template->assign_vars(array(
			'token' => $token,
			'profile' => $profile,
			'collector' => $profile->getCollector($panel),
			'panel' => $panel,
			'page' => $page,
			'request' => $request,
			'templates' => $this->get_modules($token, $profile),
			'is_ajax' => $request->isXmlHttpRequest(),
		));

		$this->template->set_filenames(array(
			'body'	=> "@nicofuma_webprofiler/collector/{$panel}_content.html",
		));

		return new Response($this->template->assign_display('body'), 200, array('Content-Type' => 'text/html'));
	}

	/**
	* Exports data for a given token.
	*
	* @param string $token The profiler token
	*
	* @return Response A Response instance
	*
	* @throws NotFoundHttpException
	*/
	public function exportAction($token)
	{
		if ($this->profiler === null) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();

		$profile = $this->profiler->loadProfile($token);
		if (!$profile) {
			throw new NotFoundHttpException(sprintf('Token "%s" does not exist.', $token));
		}

		return new Response($this->profiler->export($profile), 200, array(
			'Content-Type' => 'text/plain',
			'Content-Disposition' => 'attachment; filename= '.$token.'.txt',
		));
	}

	/**
	* Purges all tokens.
	*
	* @return Response A Response instance
	*
	* @throws NotFoundHttpException
	*/
	public function purgeAction()
	{
		if ($this->profiler === null) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();
		$this->profiler->purge();

		return new RedirectResponse($this->helper->route('_profiler_info', array('about' => 'purge')), 302, array('Content-Type' => 'text/html'));
	}

	/**
	* Imports token data.
	*
	* @param Request $request The current HTTP Request
	*
	* @return Response A Response instance
	*
	* @throws NotFoundHttpException
	*/
	public function importAction(Request $request)
	{
		if ($this->profiler === null) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();

		$file = $request->files->get('file');

		if (empty($file) || !$file->isValid()) {
			return new RedirectResponse($this->helper->route('_profiler_info', array('about' => 'upload_error')), 302, array('Content-Type' => 'text/html'));
		}

		$profile = $this->profiler->import(file_get_contents($file->getPathname()));
		if (!$profile) {
			return new RedirectResponse($this->helper->route('_profiler_info', array('about' => 'already_exists')), 302, array('Content-Type' => 'text/html'));
		}

		return new RedirectResponse($this->helper->route('_profiler', array('token' => $profile->getToken())), 302, array('Content-Type' => 'text/html'));
	}

	/**
	* Displays information page.
	*
	* @param string $about The about message
	*
	* @return Response A Response instance
	*
	* @throws NotFoundHttpException
	*/
	public function infoAction($about)
	{
		if ($this->profiler === null) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();

		$this->template->set_filenames(array(
			'body'	=> "@nicofuma_webprofiler/profiler/info.html",
		));

		$this->template->assign_vars(array(
			'about' => $about,
		));

		return new Response($this->template->assign_display('body'), 200, array('Content-Type' => 'text/html'));
	}

	/**
	* Renders the Web Debug Toolbar.
	*
	* @param Request $request The current HTTP Request
	* @param string $token The profiler token
	*
	* @return Response A Response instance
	*
	* @throws NotFoundHttpException
	*/
	public function toolbarAction(Request $request, $token)
	{
		if ($this->profiler === null) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		if ('empty' === $token || $token === null) {
			return new Response('', 200, array('Content-Type' => 'text/html'));
		}

		$this->profiler->disable();

		if (!$profile = $this->profiler->loadProfile($token)) {
			return new Response('', 404, array('Content-Type' => 'text/html'));
		}

		// the toolbar position (top, bottom, normal, or null -- use the configuration)
		if ($position = $request->query->get('position') === null) {
			$position = 'bottom';
		}

		$url = null;
		try {
			$url = $this->helper->route('_profiler', array('token' => $token));
		} catch (\Exception $e) {
			// the profiler is not enabled
		}


		$this->template->set_filenames(array(
			'body'	=> "@nicofuma_webprofiler/profiler/toolbar.html",
		));

		$this->template->assign_vars(array(
			'position' => $position,
			'profile' => $profile,
			'templates' => $this->get_modules($token, $profile),
			'profiler_url' => $url,
			'token' => $token,
		));

		return new Response($this->template->assign_display('body'), 200, array('Content-Type' => 'text/html'));
	}

	/**
	* Renders the profiler search bar.
	*
	* @param Request $request The current HTTP Request
	*
	* @return Response A Response instance
	*
	* @throws NotFoundHttpException
	*/
	public function searchBarAction(Request $request)
	{
		if ($this->profiler === null) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();

		$session = $request->getSession();
		if ($session === null) {
			$ip = null;
			$method = null;
			$url = null;
			$start = null;
			$end = null;
			$limit = null;
			$token = null;
		} else {
			$ip = $session->get('_profiler_search_ip');
			$method = $session->get('_profiler_search_method');
			$url = $session->get('_profiler_search_url');
			$start = $session->get('_profiler_search_start');
			$end = $session->get('_profiler_search_end');
			$limit = $session->get('_profiler_search_limit');
			$token = $session->get('_profiler_search_token');
		}

		$this->template->assign_vars(array(
			'token' => $token,
			'ip' => $ip,
			'method' => $method,
			'url' => $url,
			'start' => $start,
			'end' => $end,
			'limit' => $limit,
		));

		$this->template->set_filenames(array(
			'body'	=> "@nicofuma_webprofiler/profiler/search.html",
		));

		return new Response($this->template->assign_display('body'), 200, array('Content-Type' => 'text/html'));
	}

	/**
	* Search results.
	*
	* @param Request $request The current HTTP Request
	* @param string $token The token
	*
	* @return Response A Response instance
	*
	* @throws NotFoundHttpException
	*/
	public function searchResultsAction(Request $request, $token)
	{
		if ($this->profiler === null) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();

		$profile = $this->profiler->loadProfile($token);

		$ip = $request->query->get('ip');
		$method = $request->query->get('method');
		$url = $request->query->get('url');
		$start = $request->query->get('start', null);
		$end = $request->query->get('end', null);
		$limit = $request->query->get('limit');

		$tokens = $this->profiler->find($ip, $url, $limit, $method, $start, $end);
		foreach ($tokens as &$token_value)
		{
			$token_value['link'] = $this->helper->route('_profiler', array('token' => $token_value['token']));
		}

		$this->assign_layout_vars($request, $profile, $token);

		$this->template->assign_vars(array(
			'token' 		=> $token,
			'profile' 		=> $profile,
			'tokens' 		=> $tokens,
			'ip' 			=> $ip,
			'method' 		=> $method,
			'url' 			=> $url,
			'start' 		=> $start,
			'end' 			=> $end,
			'limit'			=> $limit,
			'panel' 		=> null,
		));

		$this->template->set_filenames(array(
			'body'	=> '@nicofuma_webprofiler/profiler/results.html',
		));

		return new Response($this->template->assign_display('body'), 200, array('Content-Type' => 'text/html'));
	}

	/**
	* Narrow the search bar.
	*
	* @param Request $request The current HTTP Request
	*
	* @return Response A Response instance
	*
	* @throws NotFoundHttpException
	*/
	public function searchAction(Request $request)
	{
		if ($this->profiler === null) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();

		$ip = preg_replace('/[^:\d\.]/', '', $request->query->get('ip'));
		$method = $request->query->get('method');
		$url = $request->query->get('url');
		$start = $request->query->get('start', null);
		$end = $request->query->get('end', null);
		$limit = $request->query->get('limit');
		$token = $request->query->get('token');

		$session = $request->getSession();
		if ($session !== null) {
			$session->set('_profiler_search_ip', $ip);
			$session->set('_profiler_search_method', $method);
			$session->set('_profiler_search_url', $url);
			$session->set('_profiler_search_start', $start);
			$session->set('_profiler_search_end', $end);
			$session->set('_profiler_search_limit', $limit);
			$session->set('_profiler_search_token', $token);
		}

		if (!empty($token)) {
			return new RedirectResponse($this->helper->route('_profiler', array('token' => $token)), 302, array('Content-Type' => 'text/html'));
		}

		$tokens = $this->profiler->find($ip, $url, $limit, $method, $start, $end);

		return new RedirectResponse($this->helper->route('_profiler_search_results', array(
			'token' => $tokens ? $tokens[0]['token'] : 'empty',
			'ip' => $ip,
			'method' => $method,
			'url' => $url,
			'start' => $start,
			'end' => $end,
			'limit' => $limit,
		), false), 302, array('Content-Type' => 'text/html'));
	}

	/**
	* Displays the PHP info.
	*
	* @return Response A Response instance
	*
	* @throws NotFoundHttpException
	*/
	public function phpinfoAction()
	{
		if (null === $this->profiler) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();

		ob_start();
		phpinfo();
		$phpinfo = ob_get_clean();

		return new Response($phpinfo, 200, array('Content-Type' => 'text/html'));
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
