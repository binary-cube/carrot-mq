<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Event;

/**
 * Class Event
 */
abstract class Event
{

    /**
     * @return string
     */
    abstract public static function name(): string;

}
