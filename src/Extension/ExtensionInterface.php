<?php

namespace BinaryCube\CarrotMQ\Extension;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Interface ExtensionInterface
 *
 * @package BinaryCube\CarrotMQ\Extension
 */
interface ExtensionInterface extends EventSubscriberInterface
{

    /**
     * @return string
     */
    public static function name();

    /**
     * @return string
     */
    public static function description();

}
