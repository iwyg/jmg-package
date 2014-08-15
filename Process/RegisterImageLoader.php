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

use \Selene\Module\DI\Reference;
use \Selene\Module\DI\ContainerInterface;
use \Selene\Module\DI\Processor\ProcessInterface;
use \Selene\Module\DI\Definition\ServiceDefinition;

/**
 * @class RegisterImageLoaders
 * @package Thapp\Jmg\Process
 * @version $Id$
 */
class RegisterImageLoader implements ProcessInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerInterface $container)
    {
        $this->container = $container;

        $loaders = $container->getParameter('jmg.source_loaders');

        if (2 > count($loaders)) {
            $this->registerSingleLoader(current($loaders));

            return;
        }

        $this->registerLoaders($loaders);
    }

    /**
     * registerLoaders
     *
     * @param array $loaders
     *
     * @throws \InvalidArgumentException
     * @return void
     */
    private function registerLoaders(array $loaders)
    {
        $params = $this->container->getParameters();

        $loader = new ServiceDefinition;

        $loader->setClass($params->get('jmg.loader_delegating.class'));
        $loader->setInternal(true);

        $arguments = [];

        foreach ($loaders as $loaderId) {
            if (!$this->container->hasDefinition($loaderId)) {
                $this->loaderNotFound($loaderId);
            }
            $arguments[] = new Reference($loaderId);
        };

        $arguments = [$arguments];

        $loader->setArguments($arguments);

        $this->container->setDefinition('jmg.image_loader', $loader);
    }

    /**
     * registerSingleLoader
     *
     * @throws \InvalidArgumentException
     * @return void
     */
    private function registerSingleLoader($loaderId)
    {
        if (!$this->container->hasDefinition($loaderId)) {
            $this->loaderNotFound($loaderId);
        }

        $this->container->setAlias('jmg.image_loader', $loaderId);
    }

    /**
     * loaderNotFound
     *
     * @param string $loaderId
     *
     * @throws \InvalidArgumentException
     * @return void
     */
    private function loaderNotFound($loaderId)
    {
        throw new \InvalidArgumentException(sprintf('Loader service %s does not exist', $loaderId));
    }
}
