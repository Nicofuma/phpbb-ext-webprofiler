<?php
/**
 *
 * @package NV Newspage Extension
 * @copyright (c) 2013 nickvergessen
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
