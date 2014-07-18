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

use \Selene\Components\Events\Event;

/**
 * @class ClearEvent
 * @package Thapp\Jmg\Cache\Events
 * @version $Id$
 */
class ClearEvent extends Event
{
    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
