<?php

/**
 * This file was generated at 2014-07-16 12:07:55.
 *
 * @author Thomas Appel <mail@thomas-appel.com>
 */

namespace Thapp\Jmg\Config;

use \Selene\Components\DI\BuilderInterface;
use \Selene\Components\Package\PackageConfiguration;

/**
 * @class Config
 */
class Config extends PackageConfiguration
{
    /**
     * setup
     *
     * @param ContainerInterface $container
     * @param array $values
     *
     * @return void
     */
    public function setup(BuilderInterface $builder, array $values = [])
    {
        $loader = $this->getConfigLoader($builder);
        $loader->load('services.xml');

        $config = $this->mergeValues($values);


        $this->prepareLoaders($config);
        $this->prepareDrivers($config);
        $this->prepareConstraints($config);

        $this->prepareRecipes($config);
        $this->prepareCaches($config);

        $this->prepareDynamicRoutes($config);

        $this->prepareTrustedSites($config);
        $this->prepareTemplating($config);
    }

    private function prepareTemplating(array $config)
    {
        $this->setParameter('jmg.templating', $this->getDefaultUsing($config, 'templating', function () {
            return $this->getParameter('jmg.templating');
        }));

        $this->setParameter('jmg.default_path', $this->getDefaultUsing($config, 'default_path', function () {
            $paths = array_keys($this->getParameter('jmg.paths'));
            return current($paths);
        }));
    }

    /**
     * prepareRecipes
     *
     * @param array $config
     *
     * @return void
     */
    private function prepareRecipes(array $config)
    {
        $this->setParameter('jmg.recipes', $this->getDefault($config, 'recipes', []));
    }

    /**
     * prepareCaches
     *
     * @param array $config
     *
     * @return void
     */
    private function prepareCaches(array $config)
    {
        if (!isset($config['cache'])) {
            $config['cache'] = [];
        }

        $this->setParameter('jmg.cache_enabled', $this->getDefaultUsing($config['cache'], 'enabled', function () {
            return $this->getParameter('jmg.cache_enabled');
        }));

        $this->setParameter('jmg.cache_suffix', $this->getDefaultUsing($config['cache'], 'suffix', function () {
            return $this->getParameter('jmg.cache_suffix');
        }));

        $this->setParameter('jmg.cache_path', $this->getDefaultUsing($config['cache'], 'path', function () {
            return $this->getParameter('jmg.cache_path');
        }));

        $this->setParameter('jmg.cache_paths', $this->getDefault($config['cache'], 'paths', []));
    }

    /**
     * prepareTrustedSites
     *
     * @param array $config
     *
     * @return void
     */
    private function prepareTrustedSites(array $config)
    {
        $this->setParameter('jmg.trusted_sites', $this->getDefault($config, 'trusted_sites', []));
    }

    /**
     * prepareConstraints
     *
     * @param array $config
     *
     * @return void
     */
    private function prepareConstraints(array $config)
    {
        $this->setParameter(
            'jmg.disable_dynamic_processing',
            $this->getDefault($config, 'disable_dynamic_processing', false)
        );

        $constraints = [];

        foreach ($this->getDefault($config, 'mode_constraints', []) as $key => $value) {
            if (is_array($value) && isset($value['mode']) && isset($value['values'])) {
                $constraints[$value['mode']] = $value['values'];
            } elseif (is_int($key) && is_array($value) && ctype_digit(implode(',', $value))) {
                $constraints[$key] = $value;
            }
        }

        $this->setParameter('jmg.mode_constraints', $constraints);

    }

    /**
     * prepareDynamicRoutes
     *
     * @param array $config
     *
     * @return void
     */
    private function prepareDynamicRoutes(array $config)
    {
        if ($disabled = $this->getDefault($config, 'disable_dynamic_processing', false)) {
            return;
        }
        //var_dump($this->getDefault($config, 'paths', []));

        $this->setParameter('jmg.paths', $this->getDefault($config, 'paths', []));
    }

    /**
     * prepareLoaders
     *
     * @param array $config
     *
     * @return void
     */
    private function prepareLoaders(array $config)
    {
        $loaders = $this->getDefault($config, 'loaders', ['jmg.loader_filesystem']);

        $this->setParameter('jmg.source_loaders', (array)$loaders);
    }

    /**
     * prepareDrivers
     *
     * @param array $config
     *
     * @return void
     */
    private function prepareDrivers(array $config)
    {
        $this->setParameter('jmg.driver', $this->getDefaultUsing($config, 'driver', function () {
            $this->getParameter('jmg.driver');
        }));
    }
}
