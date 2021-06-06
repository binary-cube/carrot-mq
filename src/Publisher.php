<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ;

use Throwable;
use Interop\Amqp;
use Psr\Log\LoggerInterface;
use BinaryCube\CarrotMQ\Entity\Topic;
use BinaryCube\CarrotMQ\Support\Collection;
use BinaryCube\CarrotMQ\Support\AutoWireAwareTrait;

use function max;
use function usleep;
use function is_callable;
use function call_user_func_array;

/**
 * Class Publisher
 */
class Publisher extends Core implements PublisherInterface
{
    use AutoWireAwareTrait;

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
    protected $producerWasWired = false;

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
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($id, $container, $logger);

        $this->id        = $id;
        $this->topic     = $topic;
        $this->container = $container;
        $this->config    = Collection::make(static::DEFAULTS)->merge($config)->all();

        $this->topic->setLogger($this->logger);
    }

    /**
     * @return Connection
     */
    public function connection()
    {
        return $this->topic->connection();
    }

    /**
     * @return Topic
     */
    public function topic(): Topic
    {
        return $this->topic;
    }

    /**
     * @param Amqp\AmqpMessage $message
     * @param int              $retry   How many time should it be retried, default is 1
     * @param callable|null    $onRetry A callable that is called on retries, the signature must be `function (Publisher $publisher, $exception) { ... }`
     * @param float            $delay   In seconds
     *
     * @return $this
     *
     * @throws Throwable
     */
    public function publish(Amqp\AmqpMessage $message, int $retry = 0, ?callable $onRetry = null, float $delay = 0.5)
    {
        $retry = max(0, $retry);
        $error = null;

        $this->autoWire($this->container);

        if (false === $this->producerWasWired) {
            $this->wireProducer();
        }

        do {
            try {
                // Reset error.
                $error = null;

                $this
                    ->producer
                    ->setPriority($message->getPriority())
                    ->setTimeToLive($message->getTimestamp())
                    ->send($this->topic->model(), $message);

                break;
            } catch (Throwable $exception) {
                $error = $exception;
                $this->onRetry($error, $onRetry, $delay);
            }//end try
        } while (--$retry > 0);

        if (isset($error)) {
            throw $error;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function reconnect()
    {
        $this->topic->reconnect();
        $this->wireProducer(true);

        return $this;
    }

    /**
     * @param bool $refresh
     *
     * @return Amqp\AmqpProducer
     */
    protected function wireProducer(bool $refresh = false): Amqp\AmqpProducer
    {
        if ($refresh || ! isset($this->producer)) {
            $context        = $this->topic->connection()->context(true);
            $this->producer = $context->createProducer();

            $this->producerWasWired = true;
        }

        return $this->producer;
    }

    /**
     * @param Throwable     $error
     * @param callable|null $onRetry
     * @param float         $delay
     *
     * @return void
     */
    protected function onRetry(Throwable $error, ?callable $onRetry = null, float $delay = 0.5): void
    {
        try {
            $this->reconnect();
        } catch (Throwable $exception) {
            //
        }

        if (isset($onRetry) && is_callable($onRetry)) {
            call_user_func_array($onRetry, [$this, $error]);
        }

        usleep((int) (1e6 * $delay));
    }

}
