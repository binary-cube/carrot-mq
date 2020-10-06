<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Processor;

use Interop\Amqp;
use Psr\Log\LoggerInterface;
use BinaryCube\CarrotMQ\Container;
use BinaryCube\CarrotMQ\Entity\Queue;

/**
 * Interface Processor
 */
interface Processor
{

    /**
     * Use this constant when the message is processed successfully and the message could be removed from the queue.
     */
    const ACK = 'ack';

    /**
     * Use this constant when the message is not valid or could not be processed
     * The message is removed from the queue.
     */
    const REJECT = 'reject';

    /**
     * Use this constant when the message is not valid or could not be processed right now but we can try again later
     * The original message is removed from the queue but a copy is published to the queue again.
     */
    const REQUEUE = 'requeue';

    /**
     * Use this constant when the process need to handle message ACK by himself.
     */
    const SELF_ACK = 'self.ack';

    /**
     * @param Queue                $queue
     * @param Amqp\AmqpContext     $context
     * @param Amqp\AmqpConsumer    $consumer
     * @param Container|null       $container
     * @param LoggerInterface|null $logger
     *
     * @return $this
     */
    public function mount(
        Queue $queue,
        Amqp\AmqpContext $context,
        Amqp\AmqpConsumer $consumer,
        Container $container = null,
        LoggerInterface $logger = null
    );

    /**
     * @return $this
     */
    public function unmount();

    /**
     * The method has to return either self::ACK, self::REJECT, self::REQUEUE, self::SELF_ACK string.
     *
     * @param Amqp\AmqpMessage $message The message
     * @param Amqp\AmqpContext $context The context
     *
     * @return mixed false to requeue, any other value to acknowledge
     */
    public function process(Amqp\AmqpMessage $message, Amqp\AmqpContext $context);

}
