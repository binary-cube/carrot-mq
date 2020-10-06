<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Support;

use Psr\Log\LoggerInterface;

/**
 * Basic Implementation of LoggerAwareInterface.
 */
trait LoggerAwareTrait
{

    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

}
