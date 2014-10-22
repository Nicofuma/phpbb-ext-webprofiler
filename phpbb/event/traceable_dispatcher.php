<?php
/**
 *
 * WebProfiler extension for the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace nicofuma\webprofiler\phpbb\event;

use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;

/**
* Extension of the Symfony2 TraceableEventDispatcher
*
* It collects some data about event listeners.
*
* This event dispatcher delegates the dispatching to another one.
*/
class traceable_dispatcher extends TraceableEventDispatcher implements \phpbb\event\dispatcher_interface
{
	public function trigger_event($eventName, $data = array())
	{
		$event = new \phpbb\event\data($data);
		$this->dispatch($eventName, $event);
		return $event->get_data_filtered(array_keys($data));
	}
}
