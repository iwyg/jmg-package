<?php

/**
 * This file was generated at 2014-07-16 12:07:55.
 *
 * @author Thomas Appel <mail@thomas-appel.com>
 */

namespace Thapp\Jmg;

use \Selene\Components\Package\Package;
use \Selene\Components\Package\ExportResourceInterface;
use \Selene\Components\Package\FileRepositoryInterface;
use \Selene\Components\DI\BuilderInterface;
use \Selene\Components\DI\Processor\ProcessorInterface;
use \Selene\Components\Console\Application as Console;
use \Thapp\Jmg\Process\RegisterImageLoader;
use \Thapp\Jmg\Process\RegisterImageDriver;
use \Thapp\Jmg\Process\RegisterCaches;
use \Thapp\Jmg\Process\RegisterResolver;
use \Thapp\Jmg\Process\RegisterRoutes;
use \Thapp\Jmg\Process\RegisterTwigExtension;

/**
 * @class JitImagePackage
 */
class JmgPackage extends Package implements ExportResourceInterface
{
    /**
     * build
     *
     * @param BuilderInterface $builder
     *
     * @return void
     */
    public function build(BuilderInterface $builder)
    {
        $builder->getProcessor()
            ->add(new RegisterImageLoader, ProcessorInterface::BEFORE_OPTIMIZE)
            ->add(new RegisterImageDriver, ProcessorInterface::BEFORE_OPTIMIZE)
            ->add(new RegisterCaches, ProcessorInterface::BEFORE_OPTIMIZE)
            ->add(new RegisterResolver, ProcessorInterface::OPTIMIZE)
            ->add(new RegisterTwigExtension, ProcessorInterface::BEFORE_REMOVE)
            ->add(new RegisterRoutes($builder), ProcessorInterface::BEFORE_OPTIMIZE);

    }

    /**
     * registerCommands
     *
     * @param Console $console
     *
     * @return void
     */
    public function registerCommands(Console $console)
    {
    }

    /**
     * getExports
     *
     * @param FileRepositoryInterface $files
     *
     * @return void
     */
    public function getExports(FileRepositoryInterface $files)
    {
        $files->createTarget($this->getResourcePath().'/publish/config.xml');
    }

    /**
     * requires
     *
     * @return void
     */
    public function requires()
    {
        return ['framework', 'twig?', 'phptal?'];
    }
}
