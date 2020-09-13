<?php

namespace BinaryCube\CarrotMQ\Processor;

use Interop\Amqp;
use Psr\Log\LoggerInterface;
use BinaryCube\CarrotMQ\Container;
use BinaryCube\CarrotMQ\Entity\Queue;

/**
 * Class CallbackProcessor
 */
class CallbackProcessor implements Processor
{

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var Amqp\AmqpContext
     */
    protected $context;

    /**
     * @var Amqp\AmqpConsumer
     */
    protected $consumer;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

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
    ) {
        $this->queue     = $queue;
        $this->context   = $context;
        $this->consumer  = $consumer;
        $this->container = $container;
        $this->logger    = $logger;

        return $this;
    }

    /**
     * @return $this
     */
    public function unmount()
    {
        return $this;
    }

    /**
     * The method has to return either self::ACK, self::REJECT, self::REQUEUE, self::SELF_ACK string.
     *
     * @param Amqp\AmqpMessage $message The message
     * @param Amqp\AmqpContext $context The context
     *
     * @return mixed false to requeue, any other value to acknowledge
     */
    public function process(Amqp\AmqpMessage $message, Amqp\AmqpContext $context)
    {
        return \call_user_func_array($this->callback, [$message, $context]);
    }

}
