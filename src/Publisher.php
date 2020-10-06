<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ;

use Interop\Amqp;
use Psr\Log\LoggerInterface;
use BinaryCube\CarrotMQ\Entity\Topic;
use BinaryCube\CarrotMQ\Support\Collection;

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
        $this->config    = Collection::make(static::DEFAULTS)->merge($config)->all();

        $this->topic->setLogger($this->logger);

        $context = $this->topic->connection()->context(true);

        $this->producer = $context->createProducer();
    }

    /**
     * @return Connection
     */
    public function connection()
    {
        return $this->topic->connection();
    }

    /**
     * @param Amqp\AmqpMessage $message
     * @param int              $retry   How many time should it be retried, default is 1
     * @param callable|null    $onRetry A callable that is called on retries, the signature must be `function (Publisher $publisher, $exception) { ... }`
     * @param float            $delay   In seconds
     *
     * @return $this
     *
     * @throws \Exception
     * @throws Exception\Exception
     * @throws \Interop\Queue\Exception
     */
    public function publish(Amqp\AmqpMessage $message, $retry = 0, $onRetry = null, $delay = 0.5)
    {
        $retry = \max(0, $retry);

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

        $error = null;

        do {
            try {
                $this
                    ->producer
                    ->setPriority($message->getPriority())
                    ->setTimeToLive($message->getTimestamp())
                    ->send($this->topic->model(), $message);

                break;
            } catch (\Exception $exception) {
                $error = $exception;

                if (isset($onRetry) && \is_callable($onRetry)) {
                    \call_user_func_array($onRetry, [$this, $error]);
                }

                \usleep(1e6 * $delay);
            }
        } while (--$retry > 0);

        if (isset($error)) {
            throw $error;
        }

        return $this;
    }

}
