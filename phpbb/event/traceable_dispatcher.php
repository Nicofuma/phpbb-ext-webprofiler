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

use Symfony\Component\EventDispatcher\Event;
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
	/**
	 * @var bool
	 */
	protected $disabled = false;

	/**
	 * {@inheritdoc}
	 */
	public function trigger_event($eventName, $data = array())
	{
		$event = new \phpbb\event\data($data);
		$this->dispatch($eventName, $event);
		return $event->get_data_filtered(array_keys($data));
	}

	/**
	 * {@inheritdoc}
	 */
	public function dispatch($eventName, Event $event = null)
	{
		if ($this->disabled)
		{
			return $event;
		}

		return parent::dispatch($eventName, $event);
	}

	/**
	 * {@inheritdoc}
	 */
	public function disable()
	{
		$this->disabled = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function enable()
	{
		$this->disabled = false;
	}
}
