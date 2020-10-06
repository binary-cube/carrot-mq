<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Support;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Trait DispatcherAwareTrait
 */
trait DispatcherAwareTrait
{

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @param bool $fresh To force recreate the dispatcher.
     *
     * @return EventDispatcher
     */
    protected function dispatcher($fresh = false)
    {
        if ($fresh || ! isset($this->dispatcher)) {
            $this->dispatcher = new EventDispatcher();
        }

        return $this->dispatcher;
    }

}
