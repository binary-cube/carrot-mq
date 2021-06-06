<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Event\Consumer;

use Interop\Queue\Context;
use Interop\Queue\Message;
use BinaryCube\CarrotMQ\Event\Event;
use BinaryCube\CarrotMQ\Entity\Queue;

/**
 * Class MessageReceived
 */
class MessageReceived extends Event
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
     * @return string
     */
    public static function name(): string
    {
        return 'consumer.event.message.received';
    }

    /**
     * Constructor.
     *
     * @param Queue   $queue
     * @param Context $context
     * @param Message $message
     * @param float   $receivedAt
     */
    public function __construct(
        Queue $queue,
        Context $context,
        Message $message,
        float $receivedAt
    ) {
        $this->queue      = $queue;
        $this->context    = $context;
        $this->message    = $message;
        $this->receivedAt = $receivedAt;
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
     * @return Message
     */
    public function message(): Message
    {
        return $this->message;
    }

    /**
     * @return float
     */
    public function receivedAt(): float
    {
        return $this->receivedAt;
    }

}
