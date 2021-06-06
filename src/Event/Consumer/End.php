<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Event\Consumer;

use Interop\Queue\Context;
use BinaryCube\CarrotMQ\Event\Event;
use BinaryCube\CarrotMQ\Entity\Queue;

/**
 * Class End
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
     * @return string
     */
    public static function name(): string
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
    public function queue(): Queue
    {
        return $this->queue;
    }

    /**
     * @return Context
     */
    public function context(): Context
    {
        return $this->context;
    }

    /**
     * @return float
     */
    public function startTime(): float
    {
        return $this->startTime;
    }

    /**
     * @return float
     */
    public function endTime(): float
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
