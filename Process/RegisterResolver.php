<?php

/**
 * This File is part of the Thapp\Jmg\Process package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Process;

use \Selene\Components\DI\Reference;
use \Selene\Components\DI\ContainerInterface;
use \Selene\Components\DI\Processor\ProcessInterface;

/**
 * @class RegisterDynamicRoutes
 * @package Thapp\Jmg\Process
 * @version $Id$
 */
class RegisterResolver implements ProcessInterface
{
    private $container;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerInterface $container)
    {
        $this->container = $container;

        if (!$this->container->getParameter('jmg.cache_enabled')) {
            return;
        }

        $this->registerCacheResolver();
    }

    /**
     * registerCacheResolver
     *
     * @return void
     */
    private function registerCacheResolver()
    {
        $def = $this->container->getDefinition('jmg.resolver_image');
        $def->replaceArgument(new Reference('jmg.cache_resolver'), 1);
    }
}
