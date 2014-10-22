<?php
/**
 *
 * WebProfiler extension for the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace nicofuma\webprofiler\phpbb\template;

use Symfony\Component\Stopwatch\Stopwatch;
use phpbb\template\template;

/**
* Times the time spent to render a template.
*/
class timed implements template
{
	protected $stopwatch;

	/**
	* Constructor.
	*
	* @param template $template The real template engine
	* @param Stopwatch $stopwatch A Stopwatch instance
	*/
	public function __construct(template $template, Stopwatch $stopwatch)
	{
		$this->template = $template;
		$this->stopwatch = $stopwatch;
	}

	/**
	* {@inheritdoc}
	*/
	public function clear_cache()
	{
		$this->template->clear_cache();

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function set_filenames(array $filename_array)
	{
		$this->template->set_filenames($filename_array);

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_user_style()
	{
		return $this->template->get_user_style();
	}

	/**
	* {@inheritdoc}
	*/
	public function set_style($style_directories = array('styles'))
	{
		$this->template->set_style($style_directories);

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function set_custom_style($names, $paths)
	{
		$this->template->set_custom_style($names, $paths);

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function destroy()
	{
		$this->template->destroy();

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function destroy_block_vars($blockname)
	{
		$this->template->destroy_block_vars($blockname);

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function display($handle)
	{
		$e = $this->stopwatch->start(sprintf('template (%s)', $this->get_source_file_for_handle($handle)), 'template');

		$this->template->display($handle);

		$e->stop();

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function assign_display($handle, $template_var = '', $return_content = true)
	{
		$e = $this->stopwatch->start(sprintf('template (%s)', $this->get_source_file_for_handle($handle)), 'template');

		$result = $this->template->assign_display($handle, $template_var, $return_content);

		$e->stop();

		if ($return_content)
		{
			return $result;
		}

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function assign_vars(array $vararray)
	{
		$this->template->assign_vars($vararray);

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function assign_var($varname, $varval)
	{
		$this->template->assign_var($varname, $varval);

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function append_var($varname, $varval)
	{
		$this->template->append_var($varname, $varval);

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function assign_block_vars($blockname, array $vararray)
	{
		$this->template->assign_block_vars($blockname, $vararray);

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function assign_block_vars_array($blockname, array $block_vars_array)
	{
		$this->template->assign_block_vars_array($blockname, $block_vars_array);

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function alter_block_array($blockname, array $vararray, $key = false, $mode = 'insert')
	{
		return $this->template->alter_block_array($blockname, $vararray, $key, $mode);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_source_file_for_handle($handle)
	{
		return $this->template->get_source_file_for_handle($handle);
	}
}
