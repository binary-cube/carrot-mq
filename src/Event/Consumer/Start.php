<?php

namespace BinaryCube\CarrotMQ\Event\Consumer;

use Interop\Queue\Context;
use BinaryCube\CarrotMQ\Event\Event;
use BinaryCube\CarrotMQ\Entity\Queue;

/**
 * Class Start
 */
class Start extends Event
{

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var float
     */
    private $startTime;

    /**
     * @var boolean
     */
    private $executionInterrupted;

    /**
     * @var integer
     */
    private $exitStatus;

    /**
     * @return mixed
     */
    public static function name()
    {
        return 'consumer.event.start';
    }

    /**
     * Constructor.
     *
     * @param Queue   $queue
     * @param Context $context
     * @param float   $startTime
     */
    public function __construct(
        Queue $queue,
        Context $context,
        float $startTime
    ) {
        $this->queue                = $queue;
        $this->context              = $context;
        $this->startTime            = $startTime;
        $this->executionInterrupted = false;
    }

    /**
     * @return Queue
     */
    public function queue()
    {
        return $this->queue;
    }

    /**
     * @return Context
     */
    public function context()
    {
        return $this->context;
    }

    /**
     * @return float
     */
    public function startTime()
    {
        return $this->startTime;
    }

    /**
     * @return int|null
     */
    public function exitStatus(): ?int
    {
        return $this->exitStatus;
    }

    /**
     * @return bool
     */
    public function isExecutionInterrupted(): bool
    {
        return $this->executionInterrupted;
    }

    /**
     * @param int|null $exitStatus
     *
     * @return void;
     */
    public function interruptExecution(?int $exitStatus = null): void
    {
        $this->exitStatus           = $exitStatus;
        $this->executionInterrupted = true;
    }

}
