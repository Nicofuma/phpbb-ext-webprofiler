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

use nicofuma\webprofiler\phpbb\profiler\Profiler as profiler_service;

use Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Adds tagged data_collector services to profiler service.
 *
 * @see Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\ProfilerPass
 */
class profiler_pass implements CompilerPassInterface
{
	public function process(ContainerBuilder $container)
	{
		if (false === $container->hasDefinition('nicofuma.webprofiler.profiler')) {
			return;
		}

		$definition = $container->getDefinition('nicofuma.webprofiler.profiler');

		$collectors = new \SplPriorityQueue();
		$order = PHP_INT_MAX;
		foreach ($container->findTaggedServiceIds('data_collector') as $id => $attributes) {
			$priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
			$template = null;

			if (isset($attributes[0]['template'])) {
				if (!isset($attributes[0]['id'])) {
					throw new \InvalidArgumentException(sprintf('Data collector service "%s" must have an id attribute in order to specify a template', $id));
				}
				$template = array($attributes[0]['id'], $attributes[0]['template']);
			}

			$collectors->insert(array($id, $template), array($priority, --$order));
		}

		$templates = array();
		foreach ($collectors as $collector) {
			$definition->addMethodCall('add', array(new Reference($collector[0])));
			$templates[$collector[0]] = $collector[1];
		}

		$container->setParameter('data_collector.templates', $templates);
	}
}
