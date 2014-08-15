<?php

/**
 * This File is part of the Thapp\Jmg\Cache package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Cache;

use \Selene\Module\Events\Dispatcher;
use \Thapp\Image\Cache\CacheInterface;
use \Thapp\JitImage\Resolver\ResolverInterface;
use \Thapp\Jmg\Cache\Events\ClearEvent;
use \Thapp\Jmg\Cache\Events\ClearEvents as Events;

/**
 * @class Clearer
 * @package Thapp\Jmg\Cache
 * @version $Id$
 */
class Clearer
{
    /**
     * caches
     *
     * @var ResolverInterface
     */
    private $caches;

    /**
     * events
     *
     * @var Dispatcher
     */
    private $events;

    /**
     * Constructor.
     *
     * @param ResolverInterface $caches
     */
    public function __construct(ResolverInterface $caches, Dispatcher $events)
    {
        $this->events = $events;
        $this->caches = $caches;
    }

    /**
     * Clears the cache for a specific cache instance.
     *
     * @param string $alias
     *
     * @throws \InvalidArgumentException if no cache is found.
     * @return boolean
     */
    public function clearCache($alias)
    {
        $this->doClearCache($this->findCache($alias), $alias);
    }

    /**
     * Clears the cache for a specific cache instance.
     *
     * @param string $alias
     *
     * @return boolean
     */
    public function clearAll()
    {
        foreach ($this->caches as $alias => $cache) {
            $this->doClearCache($cache, $alias);
        }

        $this->events->dispatch(Events::CLEARED_ALL, new ClearEvent('All caches cleared.'));

        return true;
    }

    /**
     * clearImage
     *
     * @param string $alias
     * @param string $image
     *
     * @return boolean
     */
    public function clearImage($image, $alias = null)
    {
        $cache = $this->findCache($alias);

        if (!$cache->delete($image)) {
            $this->events->dispatch(Events::CLEARED_INFO, new ClearEvent('Nothing to delete.'));

            return false;
        }

        $this->events->dispatch(Events::CLEARED_IMAGE, new ClearEvent(
            sprintf('Cache cleared for image "%s".', $image)
        ));

        return true;
    }

    /**
     * @param CacheInterface $cache
     *
     * @return boolean
     */
    private function doClearCache(CacheInterface $cache, $alias)
    {
        try {
            if (false !== $cache->purge()) {
                $this->events->dispatch(Events::CLEARED_CACHE, new ClearEvent(
                    sprintf('Cache "%s" was cleared.', $alias)
                ));

                return true;
            }
        } catch (\Exception $e) {
            $this->events->dispatch(Events::CLEARED_ERROR, $e->getMessage());

            throw $e;
        }

        $this->events->dispatch(Events::CLEARED_INFO, new ClearEvent('Nothing to clean.'));

        return false;
    }

    /**
     * findCache
     *
     * @param string $alias
     *
     * @throws \InvalidArgumentException if no cache is found.
     * @return CacheInterface
     */
    private function findCache($alias)
    {
        if ($cache = $this->caches->resolve($alias)) {
            return $cache;
        }

        throw new \InvalidArgumentException(sprintf('A cache with alias "%s" was not found.', $alias));
    }
}
