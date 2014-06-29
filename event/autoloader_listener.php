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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Register the autoloader for the required vendors.
*
* @package nicofuma\webprofiler\event
*/
class autoloader_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.auto_loader'	=> 'register_vendor_autoloader',
		);
	}

	public function register_vendor_autoloader($event)
	{
		require (dirname( __FILE__ ) . '/../vendor/autoload.php');
	}
}
