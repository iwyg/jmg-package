<?php

/**
 * This File is part of the Thapp\Jmg\Console package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Console;

use \Selene\Components\Console\Command;
use \Selene\Components\Events\SubscriberInterface;
use \Selene\Packages\Workspace\PackageGenerator;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputArgument;
use \Thapp\Jmg\Cache\Clearer;
use \Thapp\Jmg\Cache\Events\ClearEvent as Event;
use \Thapp\Jmg\Cache\Events\ClearEvents as Events;

/**
 * @class ClearCacheCommand
 * @package Thapp\Jmg\Console
 * @version $Id$
 */
class ClearCacheCommand extends Command implements SubscriberInterface
{
    private $clearer;

    protected $name = 'jmg:clearcache';

    /**
     * Constructor.
     *
     * @param ResolverInterface $cacheResolver
     */
    public function __construct(Clearer $clearer = null)
    {
        $this->clearer = $clearer;

        parent::__construct($this->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Clear the image cache.';
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriptions()
    {
        if (!$logger = $this->getLogger()) {

            return [];
        }

        return [
            Events::CLEARED_ALL   => 'onClearcacheCache',
            Events::CLEARED_CACHE => 'onClearcacheCache',
            Events::CLEARED_IMAGE => 'onClearcacheCache',
            Events::CLEARED_ERROR => 'onClearcacheError',
            Events::CLEARED_INFO  => 'onClearcacheInfo'
        ];
    }

    /**
     * onClearcacheCache
     *
     * @param Event $event
     *
     * @return void
     */
    public function onClearcacheCache(Event $event)
    {
        $this->getOutput()->writeln($event->getMessage());
    }

    /**
     * onClearcacheError
     *
     * @param Event $event
     *
     * @return void
     */
    public function onClearcacheError(Event $event)
    {
        $this->getLogger()->error($event->getMessage());
    }

    /**
     * onClearcacheInfo
     *
     * @param Event $event
     *
     * @return void
     */
    public function onClearcacheInfo(Event $event)
    {
        $this->getLogger()->info($event->getMessage());
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $alias = $this->getInput()->getArgument('cache');

        if ($image = $this->getInput()->getArgument('image')) {

            if (null !== $alias) {
                $this->clearer->clearImage($image, $alias);

                return;
            }
        }

        if (null !== $alias) {
            $this->clearer->clearCache($alias);

            return;
        }

        $this->clearer->clearAll();
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return [
            ['cache', InputArgument::OPTIONAL, 'The aliasd cache name', null],
            ['image', InputArgument::OPTIONAL, 'The image group to delete from the cache.', null]
        ];
    }
}
