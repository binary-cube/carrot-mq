<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Extension;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Interface ExtensionInterface
 */
interface ExtensionInterface extends EventSubscriberInterface
{

    /**
     * @return string
     */
    public static function name(): string;

    /**
     * @return string
     */
    public static function description(): string;

}
