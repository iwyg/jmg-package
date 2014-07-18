<?php

/**
 * This File is part of the Thapp\Jmg\Tests\Process package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Tests\Process;

use \Mockery as m;
use \Selene\Components\DI\Container;
use \Thapp\Jmg\Process\RegisterCaches;

/**
 * @class RegisterCachesTest
 * @package Thapp\Jmg\Tests\Process
 * @version $Id$
 */
abstract class ProcessTest extends \PHPUnit_Framework_TestCase
{
    protected $container;
    protected $parameters;

    protected function setUp()
    {
        $this->container  = $this->mockContainer();
        $this->parameters = $this->mockParameters();

        $this->container->shouldReceive('getParameters')->andReturn($this->parameters);
    }

    protected function tearDown()
    {
        m::close();
    }

    protected function mockContainer()
    {
        return m::mock('Selene\Components\DI\ContainerInterface');
    }

    protected function mockParameters()
    {
        return m::mock('Selene\Components\DI\ParameterInterface');
    }
}
