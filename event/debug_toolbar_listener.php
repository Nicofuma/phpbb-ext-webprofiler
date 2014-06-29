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

namespace nicofuma\webprofiler\event;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
* WebDebugToolbarListener injects the Web Debug Toolbar.
*
* The onKernelResponse method must be connected to the kernel.response event.
*
* The WDT is only injected on well-formed HTML (with a proper </body> tag).
* This means that the WDT is never included in sub-requests or ESI requests.
*
* @author Fabien Potencier <fabien@symfony.com>
*/
class debug_toolbar_listener implements EventSubscriberInterface
{
	const DISABLED = 1;
	const ENABLED = 2;

	protected $template;
	protected $helper;
	protected $interceptRedirects;
	protected $mode;
	protected $position;

	public function __construct(\phpbb\template\template $template, \phpbb\controller\helper $helper, $interceptRedirects = false, $mode = self::ENABLED, $position = 'bottom')
	{
		$this->template = $template;
		$this->helper = $helper;
		$this->interceptRedirects = (bool) $interceptRedirects;
		$this->mode = (int) $mode;
		$this->position = $position;
	}

	public function isEnabled()
	{
		return self::DISABLED !== $this->mode;
	}

	public function onKernelResponse(FilterResponseEvent $event)
	{
		$response = $event->getResponse();
		$request = $event->getRequest();

		if ($response->headers->has('X-Debug-Token')) {
			$response->headers->set(
				'X-Debug-Token-Link',
				$this->helper->route('_profiler', array('token' => $response->headers->get('X-Debug-Token')))
			);
		}

		// do not capture redirects or modify XML HTTP Requests
		if ($request->isXmlHttpRequest())
		{
			return;
		}

		if ($response->headers->has('X-Debug-Token') && $response->isRedirect() && $this->interceptRedirects) {
			$session = $request->getSession();
			if ($session !== null && $session->isStarted() && $session->getFlashBag() instanceof AutoExpireFlashBag) {
				// keep current flashes for one more request if using AutoExpireFlashBag
				$session->getFlashBag()->setAll($session->getFlashBag()->peekAll());
			}

			$this->template->assign_vars(array(
				'location' => $response->headers->get('Location'),
			));

			$this->template->set_filenames(array(
				'body'	=> "profiler/toolbar_redirect.html",
			));

			$response->setContent($this->template->assign_display('body'));
			$response->setStatusCode(200);
			$response->headers->remove('Location');
		}

		if (self::DISABLED === $this->mode
			|| !$response->headers->has('X-Debug-Token')
			|| $response->isRedirection()
			|| ($response->headers->has('Content-Type') && strpos($response->headers->get('Content-Type'), 'html') == false)
			|| 'html' !== $request->getRequestFormat()
		)
		{
			return;
		}

		$this->injectToolbar($response);
	}

	/**
	* Injects the web debug toolbar into the given Response.
	*
	* @param Response $response A Response instance
	*/
	protected function injectToolbar(Response $response)
	{
		$content = $response->getContent();
		$pos = strripos($content, '</body>');

		$profiler_url = $this->helper->route('_profiler', array('token' => $response->headers->get('X-Debug-Token')));

		if ($pos !== false) {
			$this->template->assign_vars(array(
				'position' => $this->position,
				'token' => $response->headers->get('X-Debug-Token'),
				'profiler_link' => $profiler_url,
				'toolbar_link' => $this->helper->route('_wdt', array('token' => $response->headers->get('X-Debug-Token'), 'profiler_url' => str_replace('/', '-', $profiler_url))),
			));

			$this->template->set_filenames(array(
				'body'	=> "@nicofuma_webprofiler/profiler/toolbar_js.html",
			));

			$toolbar = "\n" . $this->template->assign_display('body') . "\n";
			$content = substr($content, 0, $pos) . $toolbar . substr($content, $pos);
			$response->setContent($content);
		}
	}

	public static function getSubscribedEvents()
	{
		return array(
			KernelEvents::RESPONSE => array('onKernelResponse', -128),
		);
	}
}
