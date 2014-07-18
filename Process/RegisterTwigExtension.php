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
 * @class RegisterTwigExtension
 * @package Thapp\Jmg\Process
 * @version $Id$
 */
class RegisterTwigExtension implements ProcessInterface
{
    private $container;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerInterface $container)
    {
        $this->container = $container;

        $templating = $this->container->getParameter('jmg.templating');

        if (!$templating['twig'] || !$this->container->hasDefinition('twig.env')) {
            return;
        }

        $this->registerTwigExtension();
    }

    /**
     * registerTwigExtension
     *
     * @return void
     */
    private function registerTwigExtension()
    {
        $this->container->getDefinition('twig.env')
            ->addSetter('addExtension', [new Reference('jmg.extension_twig')]);
    }
}
