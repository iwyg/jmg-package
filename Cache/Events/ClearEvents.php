<?php

/**
 * This File is part of the Thapp\Jmg\Cache\Events package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Cache\Events;

/**
 * @class ClearEvents
 * @package Thapp\Jmg\Cache\Events
 * @version $Id$
 */
final class ClearEvents
{
    const CLEARED_ALL   = 'jmg.clearcache.all';
    const CLEARED_CACHE = 'jmg.clearcache.cache';
    const CLEARED_IMAGE = 'jmg.clearcache.image';
    const CLEARED_ERROR = 'jmg.clearcache.error';
    const CLEARED_INFO  = 'jmg.clearcache.info';

    private function __construct()
    {
    }
}
