<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Support;

use BinaryCube\CarrotMQ\Container;

/**
 * Trait AutoWireAwareTrait
 */
trait AutoWireAwareTrait
{

    /**
     * @var boolean
     */
    protected $wasAutoWired = false;

    /**
     * @param Container $container
     *
     * @return void
     */
    protected function autoWire(Container $container): void
    {
        if ($this->wasAutoWired()) {
            return;
        }

        foreach ($container->topics()->all() as $topic) {
            if (false === $topic->canAutoCreate()) {
                continue;
            }

            try {
                $topic->install();
            } catch (\Throwable $exception) {
                //
            }
        }

        foreach ($container->queues()->all() as $queue) {
            if (false === $queue->canAutoCreate()) {
                continue;
            }

            try {
                $queue->install();
            } catch (\Throwable $exception) {
                //
            }
        }

        $this->wasAutoWired = true;
    }

    /**
     * @return bool
     */
    public function wasAutoWired(): bool
    {
        return $this->wasAutoWired;
    }

}
