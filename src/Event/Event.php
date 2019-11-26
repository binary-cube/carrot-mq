<?php

namespace BinaryCube\CarrotMQ\Event;

/**
 * Class Event
 *
 * @package BinaryCube\CarrotMQ\Event
 */
abstract class Event
{

    /**
     * @return string
     */
    abstract public static function name();

}
