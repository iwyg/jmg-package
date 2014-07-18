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
class RegisterRoutes implements ProcessInterface
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

        $loader = $this->getLoader();
        $paths = $this->container->getParameter('jmg.paths');

        if (!$this->container->getParameter('jmg.disable_dynamic_processing')) {
            $this->setupDynamicRoutes($loader = $this->getLoader(), $paths);
        }

        $this->setupStaticRoutes($loader, $this->container->getParameter('jmg.recipes'), $paths);

        if ($this->container->getParameter('jmg.cache_enabled')) {
            $caches = $this->container->getParameter('jmg.cache_paths');

            if (empty($caches)) {
                $caches = array_keys($paths);
            } else {
                $caches = $this->filterCachePaths($caches);
            }

            $this->setupCachedRoutes(
                $loader,
                $this->container->getParameter('jmg.cache_suffix'),
                $caches
            );
        }
    }

    /**
     * setupDynamicRoutes
     *
     * @param BuilderInterface $builder
     *
     * @return void
     */
    private function setupDynamicRoutes(CallableLoader $loader, array $paths)
    {
        $loader->load(function ($routes) use ($paths) {

            list ($pattern, $params, $source, $filter) = $this->getPathRegexp();
            //$pattern = '/{params}/{source}/{filter?}';

            foreach ($paths as $path => $localPath) {
                $this->registerDynamicRoute($routes, $path, $pattern, $params, $source, $filter);
            }
        });
    }

    /**
     * setupStaticRoutes
     *
     * @param CallableLoader $loader
     * @param array $recipes
     * @param array $paths
     *
     * @return void
     */
    private function setupStaticRoutes(CallableLoader $loader, array $recipes, array $paths)
    {
        $keys = array_keys($paths);

        $routes = [];
        $resolved = [];

        foreach ($recipes as $path => $params) {
            if (!in_array($path, $keys)) {
                continue;
            }

            foreach ($params as $routeAlias => $recipe) {
                $routes[strtr($routeAlias, ['/' => '_'])] = [$path, $routeAlias, $recipe];

                $resolved[$routeAlias] = $recipe;
            }
        }

        $loader->load(function ($router) use ($routes) {
            foreach ($routes as $name => $route) {
                $this->registerStaticRoute($router, $name, $route[0], $route[1], $route[2]);
            }
        });

        $this->container->getDefinition('jmg.resolver_recipes')->setArguments(
            [
                $resolved
            ]
        );
    }

    /**
     * setupCachedRoutes
     *
     * @param CallableLoader $loader
     * @param string $suffix
     * @param string $paths
     *
     * @return void
     */
    private function setupCachedRoutes(CallableLoader $loader, $suffix, $paths)
    {
        foreach ($paths as $path) {
            $loader->load(function ($routes) use ($suffix, $path) {
                $this->registerCacheRoute($routes, $suffix, $path);
            });
        }
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
     * registerStaticRoute
     *
     * @param RouteBuilder $routes
     * @param mixed $name
     * @param mixed $path
     * @param mixed $alias
     * @param mixed $recipe
     *
     * @return void
     */
    private function registerStaticRoute(RouteBuilder $routes, $name, $path, $alias, $recipe)
    {
        $pattern = $alias . '/{source}';

        $route = $routes->get('jmg.recipes_'.$name, $pattern, []);
        $route->setAction('jmg.controller:getAlias');
        $route->setDefault('path', $path);
        $route->setDefault('alias', $alias);
    }

    /**
     * registerCacheRoute
     *
     * @param RouteBuilder $routes
     * @param mixed $prefix
     * @param mixed $path
     *
     * @access private
     * @return mixed
     */
    private function registerCacheRoute(RouteBuilder $routes, $suffix, $oPath)
    {
        $path = trim(trim($oPath, '/') . '/' . $suffix, '/');

        $name = 'jmg.' . strtr($path, ['/' => '_']);

        $route = $routes->get($name, $path . '/{key}', []);
        $route->setAction('jmg.controller:getCached');

        $route->setConstraint('key', '(.*\/){1}.*');

        $route->setDefault('suffix', $suffix);
        $route->setDefault('path', $oPath);
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

    /**
     * filterCachePaths
     *
     * @param array $caches
     *
     * @return array
     */
    private function filterCachePaths($caches)
    {
        return array_keys(array_filter($caches, function ($c) {
            if (isset($c['enabled']) && !$c['enabled']) {
                return false;
            }

            return true;
        }));
    }
}
