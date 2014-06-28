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

namespace nicofuma\webprofiler\controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
* ProfilerController.
*
* @author Fabien Potencier <fabien@symfony.com>
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
		if (null === $this->profiler) {
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
	public function panelAction(Request $request, $token)
	{
		if (null === $this->profiler) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();

		$panel = $request->query->get('panel', 'request');
		$page = $request->query->get('page', 'home');

		if (!$profile = $this->profiler->loadProfile($token)) {
			$this->template->assign_vars(array('about' => 'no_token', 'token' => $token));
			return new Response($this->template->assign_display('@nicofuma_webprofiler/profiler/info.html'), 200, array('Content-Type' => 'text/html'));
		}

		if (!$profile->hasCollector($panel)) {
			throw new NotFoundHttpException(sprintf('Panel "%s" is not available for token "%s".', $panel, $token));
		}

		$modules = array();
		foreach ($profile->getCollectors() as $collector_name => $collector)
		{
			$modules[$collector_name] = $this->helper->route('_profiler', array('token' => $token, 'panel' => $collector_name));
		}

		$this->template->assign_vars(array(
			'token' => $token,
			'profile' => $profile,
			'collector' => $profile->getCollector($panel),
			'panel' => $panel,
			'page' => $page,
			'request' => $request,
			'templates' => $modules,
			'is_ajax' => $request->isXmlHttpRequest(),
			'position' => 'normal',
			'profiler_url' => 'normal',
		));

		$this->template->set_filenames(array(
			'body'	=> "@nicofuma_webprofiler/collector/{$panel}_content.html",
		));

		return new Response($this->template->assign_display('body'), 200, array('Content-Type' => 'text/html'));
	}

	/** TODO: port
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
		if (null === $this->profiler) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();

		if (!$profile = $this->profiler->loadProfile($token)) {
			throw new NotFoundHttpException(sprintf('Token "%s" does not exist.', $token));
		}

		return new Response($this->profiler->export($profile), 200, array(
			'Content-Type' => 'text/plain',
			'Content-Disposition' => 'attachment; filename= '.$token.'.txt',
		));
	}

	/** TODO: port
	* Purges all tokens.
	*
	* @return Response A Response instance
	*
	* @throws NotFoundHttpException
	*/
	public function purgeAction()
	{
		if (null === $this->profiler) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();
		$this->profiler->purge();

		return new RedirectResponse($this->generator->generate('_profiler_info', array('about' => 'purge')), 302, array('Content-Type' => 'text/html'));
	}

	/** TODO: port
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
		if (null === $this->profiler) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();

		$file = $request->files->get('file');

		if (empty($file) || !$file->isValid()) {
			return new RedirectResponse($this->generator->generate('_profiler_info', array('about' => 'upload_error')), 302, array('Content-Type' => 'text/html'));
		}

		if (!$profile = $this->profiler->import(file_get_contents($file->getPathname()))) {
			return new RedirectResponse($this->generator->generate('_profiler_info', array('about' => 'already_exists')), 302, array('Content-Type' => 'text/html'));
		}

		return new RedirectResponse($this->generator->generate('_profiler', array('token' => $profile->getToken())), 302, array('Content-Type' => 'text/html'));
	}

	/** TODO: port
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
		if (null === $this->profiler) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();

		return new Response($this->twig->render('@WebProfiler/Profiler/info.html.twig', array(
			'about' => $about
		)), 200, array('Content-Type' => 'text/html'));
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
		if (null === $this->profiler) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		if ('empty' === $token || null === $token) {
			return new Response('', 200, array('Content-Type' => 'text/html'));
		}

		$this->profiler->disable();

		if (!$profile = $this->profiler->loadProfile($token)) {
			return new Response('', 404, array('Content-Type' => 'text/html'));
		}

		// the toolbar position (top, bottom, normal, or null -- use the configuration)
		if (null === $position = $request->query->get('position')) {
			$position = $this->toolbarPosition;
		}

		$url = null;
		try {
			$url = $this->helper->route('_profiler', array('token' => $token));
		} catch (\Exception $e) {
			// the profiler is not enabled
		}

		$modules = array();
		foreach ($profile->getCollectors() as $collector_name => $collector)
		{
			$modules[$collector_name] = $this->helper->route('_profiler', array('token' => $token, 'panel' => $collector_name));
		}

		return array(
			'position' => $position,
			'profile' => $profile,
			'templates' => $modules,
			'profiler_url' => $url,
			'token' => $token,
		);
	}

	/** TODO: port
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
		if (null === $this->profiler) {
			throw new NotFoundHttpException('The profiler must be enabled.');
		}

		$this->profiler->disable();

		if (null === $session = $request->getSession()) {
			$ip =
			$method =
			$url =
			$start =
			$end =
			$limit =
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

		return new Response($this->twig->render('@WebProfiler/Profiler/search.html.twig', array(
			'token' => $token,
			'ip' => $ip,
			'method' => $method,
			'url' => $url,
			'start' => $start,
			'end' => $end,
			'limit' => $limit,
		)), 200, array('Content-Type' => 'text/html'));
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
		if (null === $this->profiler) {
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
			'admin_action' 	=> $this->helper->route('_profiler_import'),
			'purge_link' 	=> $this->helper->route('_profiler_purge', array('token' => $token)),
			'export_link' 	=> $this->helper->route('_profiler_export', array('token' => $token)),
			'search_link' 	=> $this->helper->route('_profiler_search', array('limit' => 10)),
			'search_bar_path' => $this->helper->route('_profiler_search_bar'),
		));

		$this->template->set_filenames(array(
			'body'	=> '@nicofuma_webprofiler/profiler/results.html',
		));

		return new Response($this->template->assign_display('body'), 200, array('Content-Type' => 'text/html'));
	}

	/** TODO: port
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
		if (null === $this->profiler) {
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

		if (null !== $session = $request->getSession()) {
			$session->set('_profiler_search_ip', $ip);
			$session->set('_profiler_search_method', $method);
			$session->set('_profiler_search_url', $url);
			$session->set('_profiler_search_start', $start);
			$session->set('_profiler_search_end', $end);
			$session->set('_profiler_search_limit', $limit);
			$session->set('_profiler_search_token', $token);
		}

		if (!empty($token)) {
			return new RedirectResponse($this->generator->generate('_profiler', array('token' => $token)), 302, array('Content-Type' => 'text/html'));
		}

		$tokens = $this->profiler->find($ip, $url, $limit, $method, $start, $end);

		return new RedirectResponse($this->generator->generate('_profiler_search_results', array(
			'token' => $tokens ? $tokens[0]['token'] : 'empty',
			'ip' => $ip,
			'method' => $method,
			'url' => $url,
			'start' => $start,
			'end' => $end,
			'limit' => $limit,
		)), 302, array('Content-Type' => 'text/html'));
	}

	/** TODO: port
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

	/** TODO: port
	* Gets the Template Manager.
	*
	* @return TemplateManager The Template Manager
	*/
	protected function getTemplateManager()
	{
		if (null === $this->templateManager) {
			$this->templateManager = new TemplateManager($this->profiler, $this->twig, $this->templates);
		}

		return $this->templateManager;
	}
}
