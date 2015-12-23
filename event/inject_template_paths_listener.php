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

namespace nicofuma\webprofiler\event;

use phpbb\cache\driver\driver_interface;
use phpbb\filesystem\filesystem_interface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class inject_template_paths_listener implements EventSubscriberInterface
{
	/** @var \Twig_Loader_Filesystem */
	private $loader;

	/** @var driver_interface */
	private $cache;

	/** @var filesystem_interface */
	private $filesystem;

	/** @var string */
	private $phpbb_root_path;

	/**
	 * inject_template_paths_listener constructor.
	 *
	 * @param \Twig_Loader_Filesystem $loader
	 * @param driver_interface $cache
	 * @param filesystem_interface $filesystem
	 * @param string $phpbb_root_path
	 */
	public function __construct(\Twig_Loader_Filesystem $loader, driver_interface $cache, filesystem_interface $filesystem, $phpbb_root_path)
	{
		$this->loader = $loader;
		$this->cache = $cache;
		$this->filesystem = $filesystem;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	public function on_common()
	{
		$root = false;
		if ($this->cache->_exists('nicofuma.webprofiler.twig_path.WebProfiler'))
		{
			$root = $this->cache->get('nicofuma.webprofiler.twig_path.WebProfiler');
		}

		if ($root === false)
		{
			$finder = new Finder();
			$finder
				->files()
				->name('WebProfilerBundle.php')
				->followLinks()
				->in($this->phpbb_root_path)
			;

			/** @var SplFileInfo $file */
			foreach ($finder as $file)
			{
				$root = $this->filesystem->realpath($file->getPath() . '/Resources/views');
				$this->cache->put('nicofuma.webprofiler.twig_path.WebProfiler', $root);
			}
		}

		if ($root !== false)
		{
			$this->loader->addPath($root, 'WebProfiler');
		}
	}

	public static function getSubscribedEvents()
	{
		return array(
			'core.common'	=> ['on_common'],
		);
	}
}
