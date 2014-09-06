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

namespace nicofuma\webprofiler\phpbb\routing;

use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;

/**
* Class url_matcher
*
* Implements a wrapper to allow the UrlMatcher to be available as a service.
*/
class url_matcher implements UrlMatcherInterface
{
	/**
	* @var UrlMatcherInterface
	*/
	protected $url_matcher;

	function __construct($request, $extension_manager, $phpbb_root_path, $php_ex)
	{
		$context = new RequestContext();
		$context->fromRequest($request);

		$this->url_matcher = phpbb_get_url_matcher($extension_manager, $context, $phpbb_root_path, $php_ex);
	}

	/**
	* Sets the request context.
	*
	* @param RequestContext $context The context
	*
	* @api
	*/
	public function setContext(RequestContext $context)
	{
		$this->url_matcher->setContext($context);
	}

	/**
	* Gets the request context.
	*
	* @return RequestContext The context
	*
	* @api
	*/
	public function getContext()
	{
		return $this->url_matcher->getContext();
	}

	/**
	* Tries to match a URL path with a set of routes.
	*
	* If the matcher can not find information, it must throw one of the exceptions documented
	* below.
	*
	* @param string $pathinfo The path info to be parsed (raw format, i.e. not urldecoded)
	*
	* @return array An array of parameters
	*
	* @throws Symfony\Component\Routing\Exception\ResourceNotFoundException If the resource could not be found
	* @throws Symfony\Component\Routing\Exception\MethodNotAllowedException If the resource was found but the request method is not allowed
	*
	* @api
	*/
	public function match($pathinfo)
	{
		return $this->url_matcher->match($pathinfo);
	}
}
