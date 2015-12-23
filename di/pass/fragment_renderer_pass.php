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

namespace nicofuma\webprofiler\di\pass;

use Symfony\Component\HttpKernel\DependencyInjection\FragmentRendererPass;

class fragment_renderer_pass extends FragmentRendererPass
{
	public function __construct()
	{
		parent::__construct('nicofuma.webprofiler.fragment.handler', 'kernel.fragment_renderer');
	}
}
