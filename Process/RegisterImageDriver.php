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

use \Selene\Module\DI\ContainerInterface;
use \Selene\Module\DI\Definition\ServiceDefinition;
use \Selene\Module\DI\Processor\ProcessInterface;

/**
 * @class RegisterImageLoaders
 * @package Thapp\Jmg\Process
 * @version $Id$
 */
class RegisterImageDriver implements ProcessInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerInterface $container)
    {
        $driver = $container->getParameter('jmg.driver');

        if (!in_array($driver, ['gd', 'im', 'imagick'])) {
            throw new \InvalidArgumentException(sprintf('Invalid driver %s', $driver));
        }

        $container->setAlias($alias = 'jmg.image_driver', 'jmg.driver_'.$driver);
    }
}
