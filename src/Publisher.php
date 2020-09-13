<?php

namespace BinaryCube\CarrotMQ;

use Interop\Amqp;
use Psr\Log\LoggerInterface;
use BinaryCube\CarrotMQ\Entity\Topic;

/**
 * Class Publisher
 */
class Publisher extends Core implements PublisherInterface
{

    /**
     * @const array Default queue parameters
     */
    const DEFAULTS = [];

    /**
     * @var Topic
     */
    protected $topic;

    /**
     * @var Amqp\AmqpProducer
     */
    protected $producer;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var boolean
     */
    protected $isTopicInstalled = false;

    /**
     * Constructor.
     *
     * @param string               $id
     * @param Topic                $topic
     * @param Container            $container
     * @param array                $config
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        string $id,
        Topic $topic,
        Container $container,
        array $config = [],
        $logger = null
    ) {
        parent::__construct($id, $container, $logger);

        $this->id        = $id;
        $this->container = $container;
        $this->topic     = $topic;
        $this->config    = Config::create(static::DEFAULTS)->mergeWith($config)->toArray();

        $this->topic->logger($this->logger);

        $context = $this->topic->connection()->context(true);

        $this->producer = $context->createProducer();
    }

    /**
     * @param Amqp\AmqpMessage $message
     *
     * @return $this
     *
     * @throws \Throwable
     * @throws Exception\Exception
     */
    public function publish(Amqp\AmqpMessage $message)
    {
        /*
        |--------------------------------------------------------------------------
        | Create the topic
        |--------------------------------------------------------------------------
        |
        */
        if (! $this->isTopicInstalled) {
            if ($this->topic->canAutoCreate()) {
                $this->topic->install();
            }

            $this->isTopicInstalled = true;
        }

        $this
            ->producer
            ->setPriority($message->getPriority())
            ->setTimeToLive($message->getTimestamp())
            ->send($this->topic->model(), $message);

        return $this;
    }

}
