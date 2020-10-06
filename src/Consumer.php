<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ;

use Interop\Amqp;
use Psr\Log\LoggerInterface;
use BinaryCube\CarrotMQ\Event;
use BinaryCube\CarrotMQ\Entity;
use BinaryCube\CarrotMQ\Support\Collection;
use BinaryCube\CarrotMQ\Extension\Extension;
use BinaryCube\CarrotMQ\Exception\Exception;
use BinaryCube\CarrotMQ\Support\DispatcherAwareTrait;
use BinaryCube\CarrotMQ\Collection\ExtensionRepository;
use BinaryCube\CarrotMQ\Exception\StopConsumerException;

/**
 * Class Consumer
 */
class Consumer extends Core implements ConsumerInterface
{
    use DispatcherAwareTrait;

    /**
     * @const array Default consumer parameters
     */
    const DEFAULTS = [
        // In Seconds.
        'receive_timeout' => 30,

        'qos' => [
            'enabled'        => false,
            'prefetch_size'  => 0,
            'prefetch_count' => 0,
            'global'         => false,
        ],
    ];

    /**
     * @var string
     */
    protected $tag;

    /**
     * @var Entity\Queue
     */
    protected $queue;

    /**
     * @var Processor\Processor
     */
    protected $processor;

    /**
     * @var ExtensionRepository
     */
    protected $extensions;

    /**
     * @var array
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param string               $id
     * @param Entity\Queue         $topic
     * @param Processor\Processor  $processor
     * @param Container            $container
     * @param array                $config
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        string $id,
        Entity\Queue $topic,
        Processor\Processor $processor,
        Container $container,
        array $config = [],
        $logger = null
    ) {
        parent::__construct($id, $container, $logger);

        $this->id         = $id;
        $this->container  = $container;
        $this->queue      = $topic;
        $this->processor  = $processor;
        $this->config     = Collection::make(static::DEFAULTS)->merge($config)->all();
        $this->extensions = new ExtensionRepository();

        $this->tag =
            ! empty($this->tag) ?
                $this->tag :
                \vsprintf('[host: %s]-[pid: %s]-[queue: %s]', [\gethostname(), \getmypid(), $this->queue->name()]);

        $this->queue->setLogger($this->logger);
    }

    /**
     * Get or set the tag.
     *
     * @param null|string $name
     *
     * @return $this|string
     */
    public function tag($name = null)
    {
        if (isset($name)) {
            $this->tag = $name;
            return $this;
        }

        return $this->tag;
    }

    /**
     * @return ExtensionRepository
     */
    public function extensions()
    {
        return $this->extensions;
    }

    /**
     * @return Entity\Queue
     */
    public function queue()
    {
        return $this->queue;
    }

    /**
     * @return Connection
     */
    public function connection()
    {
        return $this->queue->connection();
    }

    /**
     * @return $this
     *
     * @throws Exception
     */
    protected function prepare()
    {
        $this->dispatcher(true);

        foreach ($this->extensions->all() as $extension) {
            if (! \is_subclass_of($extension, Extension::class)) {
                throw new Exception(
                    \vsprintf(
                        'The given class, "%s", is not an instance of "%s"',
                        [
                            \get_class($extension),
                            Extension::class,
                        ]
                    )
                );
            }

            /**
             * @var Extension $extension
             */
            $extension->setLogger($this->logger);

            $this->dispatcher()->addSubscriber($extension);
        }

        /*
        |--------------------------------------------------------------------------
        | Setup Queue
        |--------------------------------------------------------------------------
        |
        */
        $this->queue->setLogger($this->logger);

        if ($this->queue->canAutoCreate()) {
            $this->queue->install();
        }

        return $this;
    }

    /**
     * @return int
     *
     * @throws \Exception
     */
    public function consume()
    {
        $startTime      = \microtime(true);
        $receiveTimeout = $this->config['receive_timeout'];
        $qos            = $this->config['qos'];

        $this->logger->debug(
            \vsprintf(
                'Consumer "%s" start consuming queue "%s" ("%s")',
                [$this->id(), $this->queue->id(), $this->queue->name()]
            )
        );

        $this->prepare();

        $context = $this->queue->connection()->context(true);

        if ($qos['enabled']) {
            $context->setQos((int) $qos['prefetch_size'], (int) $qos['prefetch_count'], (bool) $qos['global']);
        }

        $consumer = $context->createConsumer($this->queue->model());

        /**
         * @var Amqp\AmqpSubscriptionConsumer $subscription
         */
        $subscription = $context->createSubscriptionConsumer();

        /*
        |--------------------------------------------------------------------------
        | Consumer tag
        |--------------------------------------------------------------------------
        |
        */
        $consumer->setConsumerTag($this->tag);

        /*
        |--------------------------------------------------------------------------
        | Mounting the processor.
        |--------------------------------------------------------------------------
        |
        */
        $this->processor->mount($this->queue, $context, $consumer, $this->container, $this->logger);

        /*
        |--------------------------------------------------------------------------
        | Message Receiver
        |--------------------------------------------------------------------------
        |
        */
        $subscription->subscribe(
            $consumer,
            function ($message, $consumer) use ($context) {
                return $this->handle($message, $consumer, $context);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Start Event
        |--------------------------------------------------------------------------
        |
        */
        $startEvent = new Event\Consumer\Start($this->queue, $context, $startTime);
        $this->dispatcher()->dispatch($startEvent, Event\Consumer\Start::name());

        if ($startEvent->isExecutionInterrupted()) {
            $this->end($this->queue, $context, $startTime, $startEvent->exitStatus(), $subscription);
            return 0;
        }

        while (true) {
            try {
                $subscription->consume((int) ($receiveTimeout * 1e3));

                /*
                |--------------------------------------------------------------------------
                | Idle Event.
                |--------------------------------------------------------------------------
                |
                */
                $idleEvent = new Event\Consumer\Idle($this->queue, $context);
                $this->dispatcher()->dispatch($idleEvent, Event\Consumer\Idle::name());

                if ($idleEvent->isExecutionInterrupted()) {
                    $this->end($this->queue, $context, $startTime, $idleEvent->exitStatus(), $subscription);
                    return 0;
                }
            } catch (StopConsumerException $exception) {
                $this->end($this->queue, $context, $startTime, null, $subscription);
                return 0;
            } catch (\Exception $exception) {
                $this->end($this->queue, $context, $startTime, 0, $subscription);
                throw $exception;
            }//end try
        }//end while

        return 0;
    }

    /**
     * @param Amqp\AmqpMessage  $message
     * @param Amqp\AmqpConsumer $consumer
     * @param Amqp\AmqpContext  $context
     *
     * @return bool
     *
     * @throws StopConsumerException
     */
    private function handle($message, $consumer, $context)
    {
        $receivedAt = \microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Message Received Event.
        |--------------------------------------------------------------------------
        |
        */
        $messageReceivedEvent = new Event\Consumer\MessageReceived($this->queue, $context, $message, $receivedAt);
        $this->dispatcher()->dispatch($messageReceivedEvent, Event\Consumer\MessageReceived::name());

        $result = $this->processor->process($message, $context);

        switch ($result) {
            case Processor\Processor::ACK:
                $consumer->acknowledge($message);
                break;

            case Processor\Processor::REJECT:
                $consumer->reject($message, false);
                break;

            case Processor\Processor::REQUEUE:
                $consumer->reject($message, true);
                break;

            case Processor\Processor::SELF_ACK:
                break;

            default:
                throw new \LogicException(\vsprintf('Status is not supported: %s', [$result]));
                break;
        }

        /*
        |--------------------------------------------------------------------------
        | After Message Received Event.
        |--------------------------------------------------------------------------
        |
        */
        $afterMessageReceived = new Event\Consumer\AfterMessageReceived($this->queue, $context, $message, $receivedAt, $result);
        $this->dispatcher()->dispatch($afterMessageReceived, Event\Consumer\AfterMessageReceived::name());

        if ($afterMessageReceived->isExecutionInterrupted()) {
            throw new StopConsumerException();
        }

        return true;
    }

    /**
     * @param Entity\Queue                       $queue
     * @param Amqp\AmqpContext                   $context
     * @param float                              $startTime
     * @param int|null                           $exitStatus
     * @param Amqp\AmqpSubscriptionConsumer|null $subscription
     *
     * @return $this
     */
    private function end(
        Entity\Queue $queue,
        Amqp\AmqpContext $context,
        float $startTime,
        ?int $exitStatus = null,
        Amqp\AmqpSubscriptionConsumer $subscription = null
    ) {
        $endTime = \microtime(true);

        $endEvent = new Event\Consumer\End($queue, $context, $startTime, $endTime, $exitStatus);
        $this->dispatcher()->dispatch($endEvent, Event\Consumer\End::name());

        try {
            $this->processor->unmount();
        } catch (\Exception $exception) {
            // Do nothing.
        }

        if ($subscription) {
            $subscription->unsubscribeAll();
        }

        try {
            $this->queue->connection()->close();
        } catch (\Exception $exception) {
            // Do nothing.
        }

        $this->logger->debug(
            \vsprintf(
                'Consumer "%s" stop consuming queue "%s" ("%s")',
                [$this->id(), $this->queue->id(), $this->queue->name()]
            )
        );

        return $this;
    }

}
