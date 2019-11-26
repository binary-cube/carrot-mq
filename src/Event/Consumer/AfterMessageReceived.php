<?php

namespace BinaryCube\CarrotMQ\Event\Consumer;

use Interop\Queue\Context;
use Interop\Queue\Message;
use BinaryCube\CarrotMQ\Event\Event;
use BinaryCube\CarrotMQ\Entity\Queue;

/**
 * Class AfterMessageReceived
 *
 * @package BinaryCube\CarrotMQ\Event\Consumer
 */
class AfterMessageReceived extends Event
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
     * @var Message
     */
    private $message;

    /**
     * @var float
     */
    private $receivedAt;

    /**
     * @var string
     */
    private $result;

    /**
     * @var boolean
     */
    private $executionInterrupted;

    /**
     * @var integer
     */
    private $exitStatus;

    /**
     * @return string
     */
    public static function name()
    {
        return 'consumer.event.after.message.received';
    }

    /**
     * Constructor.
     *
     * @param Queue   $queue
     * @param Context $context
     * @param Message $message
     * @param float   $receivedAt
     * @param string  $result
     */
    public function __construct(
        Queue $queue,
        Context $context,
        Message $message,
        float $receivedAt,
        string $result
    ) {
        $this->queue                = $queue;
        $this->context              = $context;
        $this->message              = $message;
        $this->receivedAt           = $receivedAt;
        $this->result               = $result;
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
     * @return Message
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * @return float
     */
    public function receivedAt()
    {
        return $this->receivedAt;
    }

    /**
     * @return string
     */
    public function result()
    {
        return $this->result;
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
