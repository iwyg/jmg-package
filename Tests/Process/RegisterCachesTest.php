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
use \Thapp\Jmg\Process\RegisterCaches;
use \Selene\Components\DI\ContainerInterface;

/**
 * @class RegisterCachesTest
 * @package Thapp\Jmg\Tests\Process
 * @version $Id$
 */
class RegisterCachesTest extends ProcessTest
{
    /** @test */
    public function itShouldDoNothingWhenCachingIsDisabled()
    {
        $called = false;

        $this->container
            ->shouldReceive('getParameter')
            ->with('jmg.cache_enabled')
            ->andReturnUsing(function () use (&$called) {
                $called = true;
                return false;
            });

        $proc = new RegisterCaches;

        $proc->process($this->container);

        $this->assertTrue($called);
    }

    /** @test */
    public function itShouldRegisterDefaultCacheIfCachePathsAreEmpty()
    {
        $called = false;

        $this->container
            ->shouldReceive('getParameter')
            ->with('jmg.cache_enabled')
            ->andReturnUsing(function () use (&$called) {
                $called = true;
                return true;
            });

        $this->container
            ->shouldReceive('getParameter')
            ->with('jmg.cache_paths')
            ->andReturnUsing(function () use (&$called) {
                $called = true;
                return [];
            });

        $this->container
            ->shouldReceive('getDefinition')
            ->with('jmg.cache_resolver')
            ->andReturn($resolver = m::mock('Resolver'));

        $this->container
            ->shouldReceive('getParameter')
            ->with('jmg.paths')
            ->andReturn(['image' => __DIR__]);

        $this->container
            ->shouldReceive('getParameter')
            ->with('jmg.cache_path')
            ->andReturn(__DIR__);

        $this->container
            ->shouldReceive('getDefinition')
            ->with('jmg.cache_filesystem')
            ->andReturn($cache = m::mock('Cache'));
        $cache
            ->shouldReceive('setArguments')
            ->with([__DIR__, __DIR__]);

        $cache
            ->shouldReceive('setScope')
            ->with(ContainerInterface::SCOPE_CONTAINER);

        $resolver
            ->shouldReceive('addSetter')
            ->with('add', m::any())
            ->andReturnUsing(function ($method, $args) {
                $this->assertInstanceof('Selene\Components\DI\Reference', $args[1]);
                $this->assertSame('jmg.cache_filesystem', (string)$args[1]);
            });

        $proc = new RegisterCaches;

        $proc->process($this->container);
    }
}
