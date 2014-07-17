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
use \Selene\Components\DI\Definition\ServiceDefinition;
use \Selene\Components\DI\Processor\ProcessInterface;

/**
 * @class RegisterCaches
 * @package Thapp\Jmg\Process
 * @version $Id$
 */
class RegisterCaches implements ProcessInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerInterface $container)
    {
        $this->container = $container;

        if (!$this->container->getParameter('jmg.cache_enabled')) {
            return;
        }

        var_dump($this->container->getParameter('jmg.cache_paths'));

        if (!($paths = $this->container->getParameter('jmg.cache_paths')) || empty($paths)) {
            $this->registerDefaultCache();

            return;
        }

        $this->registerCaches($paths);
    }

    /**
     * registerDefaultCache
     *
     * @return void
     */
    private function registerDefaultCache()
    {
        var_dump('default cache');
        die;

        $resolver = $this->container->getDefinition('jmg.cache_resolver');
        $cache    = $this->container->getDefinition('jmg.cache_filesystem');
        $cache->setScope(ContainerInterface::SCOPE_CONTAINER);

        $cache->setArguments([
            $location = $this->container->getParameter('jmg.cache_path'),
            $location
            ]);

        foreach ($this->container->getParameter('jmg.paths') as $path => $location) {
            $resolver->addSetter('add', [$path, new Reference('jmg.cache_filesystem')]);
        }
    }

    /**
     * registerCaches
     *
     * @param array $paths
     *
     * @return void
     */
    private function registerCaches(array $paths)
    {
        $services = [];

        $resolver = $this->container->getDefinition('jmg.cache_resolver');

        foreach ($paths as $path => $cache) {

            $service = 'jmg.cache_filesystem';

            if (isset($cache['enabled']) && true !== $cache['enabled']) {
                continue;
            }

            if (isset($cache['service'])) {

                $services[] = $cache['service'];

                if ($this->isKnowenCache($cache['service'])) {
                    $this->setupKnowenCache($cache['service'], $path, $cache, $resolver);
                } else {
                    $this->setupForeignCache($cache['service'], $path, $cache, $resolver);
                }
            } else {
                $this->setupKnowenCache($service, $path, $cache, $resolver);
            }
        }

        $this->prepareMemcachedClinet($services);
    }

    /**
     * prepareMemcachedClinet
     *
     * @param array $services
     *
     * @return void
     */
    private function prepareMemcachedClinet(array $services)
    {
        if (!in_array('jmg.cache_hybrid', $services) || !in_array('jmg.cache_memcached', $services)) {
            return;
        }
    }

    /**
     * setupKnowenCache
     *
     * @param string $service
     * @param string $path
     * @param array $cache
     * @param ServiceDefinition $resolver
     *
     * @return void
     */
    private function setupKnowenCache($service, $path, array $cache, ServiceDefinition $resolver)
    {
        $id = $service . '_' . strtr($path, ['/' => '_']);

        $location = isset($cache['path']) ? $cache['path'] : $this->container->getParameter('jmg.cache_path');

        $def = new ServiceDefinition($service.'.class');
        $def->merge($this->container->getDefinition($service));

        if ('jmg.cache_filesystem' === $service) {
            $def->setArguments([$location, $location]);
        } elseif ('jmg.cache_hybrid' === $service) {
            $args = $def->getArguments();
            $def->setArguments([$args[0], $location]);
        }

        $def->setScope(ContainerInterface::SCOPE_CONTAINER);

        $this->container->setDefinition($id, $def);

        $resolver->addSetter('add', [$path, new Reference($id)]);
    }

    /**
     * setupForeignCache
     *
     * @param string $service
     * @param string $path
     * @param array  $cache
     * @param ServiceDefinition $resolver
     *
     * @return void
     */
    private function setupForeignCache($service, $path, array $cache, ServiceDefinition $resolver)
    {
        if (!$this->container->hasDefinition($service)) {
            throw new \InvalidArgumentException(sprintf('Service "%s" is not defined.', $service));
        }

        $resolver->addSetter('add', [$path, new Reference($service)]);
    }

    /**
     * isKnowenCache
     *
     * @param string $service
     *
     * @return boolean
     */
    private function isKnowenCache($service)
    {
        return in_array($service, ['jmg.cache_filesystem', 'jmg.cache_hybrid', 'jmg.cache_memcached']);
    }
}
