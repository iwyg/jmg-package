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

use \Thapp\JitImage\ProviderTrait;
use \Selene\Components\DI\BuilderInterface;
use \Selene\Components\DI\ContainerInterface;
use \Selene\Components\DI\Definition\ServiceDefinition;
use \Selene\Components\DI\Processor\ProcessInterface;
use \Selene\Components\Routing\Loader\CallableLoader;
use \Selene\Components\Routing\RouteBuilder;
use \Selene\Components\Routing\Route;

/**
 * @class RegisterDynamicRoutes
 * @package Thapp\Jmg\Process
 * @version $Id$
 */
class RegisterDynamicRoutes implements ProcessInterface
{
    use ProviderTrait;

    private $builder;
    private $container;

    /**
     * Constructor.
     *
     * @param BuilderInterface $builder
     */
    public function __construct(BuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerInterface $container)
    {
        $this->container = $container;

        if (!$this->container->hasDefinition('routing.routes')) {
            return;
        }

        $this->setupDynamicRoutes();
    }

    /**
     * setupDynamicRoutes
     *
     * @param BuilderInterface $builder
     *
     * @return void
     */
    private function setupDynamicRoutes()
    {
        $loader = $this->getLoader();

        $paths = $this->container->getParameter('jmg.paths');

        $loader->load(function ($routes) use ($paths) {

            list ($pattern, $params, $source, $filter) = $this->getPathRegexp();
            //$pattern = '/{params}/{source}/{filter?}';

            foreach ($paths as $path => $localPath) {
                $this->registerDynamicRoute($routes, $path, $pattern, $params, $source, $filter);
            }
        });
    }

    /**
     * registerDynamicRoute
     *
     * @param string $path
     *
     * @return void
     */
    private function registerDynamicRoute(
        RouteBuilder $routes,
        $path,
        $pattern,
        $paramsRegxp,
        $sourceRegxp,
        $filterRegxp
    ) {
        $route = $routes->get('jmg.'.strtr($path, ['/' => '.']), rtrim($path, '/').$pattern, []);

        $route->setAction('jmg.controller:getImage');

        $route->setConstraint('params', $paramsRegxp);
        $route->setConstraint('source', $sourceRegxp);
        $route->setConstraint('filter', $filterRegxp);

        $route->setDefault('filter', null);
        $route->setDefault('path', $path);
    }

    /**
     * getLoader
     *
     * @return CallableLoader
     */
    private function getLoader()
    {
        return new CallableLoader($this->builder, $this->container->get('routing.routes'));
    }
}
