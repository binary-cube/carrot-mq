<?php

namespace BinaryCube\CarrotMQ\Event\Consumer;

use Interop\Queue\Context;
use BinaryCube\CarrotMQ\Event\Event;
use BinaryCube\CarrotMQ\Entity\Queue;

/**
 * Class End
 *
 * @package BinaryCube\CarrotMQ\Event\Consumer
 */
class End extends Event
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
     * @var float
     */
    private $endTime;

    /**
     * @var integer
     */
    private $exitStatus;

    /**
     * @return mixed
     */
    public static function name()
    {
        return 'consumer.event.end';
    }

    /**
     * Constructor.
     *
     * @param Queue    $queue
     * @param Context  $context
     * @param float    $startTime
     * @param float    $endTime
     * @param int|null $exitStatus
     */
    public function __construct(
        Queue $queue,
        Context $context,
        float $startTime,
        float $endTime,
        ?int $exitStatus = null
    ) {
        $this->queue      = $queue;
        $this->context    = $context;
        $this->startTime  = $startTime;
        $this->endTime    = $endTime;
        $this->exitStatus = $exitStatus;
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
     * @return float
     */
    public function endTime()
    {
        return $this->endTime;
    }

    /**
     * @return int|null
     */
    public function getExitStatus(): ?int
    {
        return $this->exitStatus;
    }

}
