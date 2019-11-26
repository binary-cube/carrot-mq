<?php

namespace BinaryCube\CarrotMQ\Builder;

use Psr\Log\LoggerInterface;
use BinaryCube\CarrotMQ\Consumer;
use BinaryCube\CarrotMQ\Publisher;
use BinaryCube\CarrotMQ\Component;
use BinaryCube\CarrotMQ\Container;
use BinaryCube\CarrotMQ\Connection;
use BinaryCube\CarrotMQ\Entity\Queue;
use BinaryCube\CarrotMQ\Entity\Topic;
use BinaryCube\CarrotMQ\Processor\Processor;
use BinaryCube\CarrotMQ\Processor\CallbackProcessor;
use BinaryCube\CarrotMQ\Exception\InvalidConfigException;

/**
 * Class ContainerBuilder
 *
 * @package BinaryCube\CarrotMQ\Builder
 */
class ContainerBuilder extends Component
{

    /**
     * @const array Default parameters.
     */
    const DEFAULTS = [
        'connections' => [],
        'topics'      => [],
        'queues'      => [],
        'publishers'  => [],
        'consumers'   => [],
    ];

    /**
     * @param array                $config
     * @param LoggerInterface|null $logger
     *
     * @return Container
     */
    public static function create(array $config, $logger = null)
    {
        $builder = new static($logger);

        return $builder->build($config);
    }

    /**
     * Constructor.
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct($logger = null)
    {
        parent::__construct(null, $logger);
    }

    /**
     * @param array $config
     *
     * @return Container
     */
    public function build(array $config)
    {
        $config = \array_merge(static::DEFAULTS, $config);

        $container = new Container();

        $this
            ->createConnections($container, $config)
            ->createTopics($container, $config)
            ->createQueues($container, $config)
            ->createPublishers($container, $config)
            ->createConsumers($container, $config);

        return $container;
    }

    /**
     * @param Container $container
     * @param array     $config
     *
     * @return $this
     */
    protected function createConnections(Container $container, array  $config)
    {
        $connections = $config['connections'];

        foreach ($connections as $id => $connection) {
            $entry = new Connection($id, $connection, $this->logger);

            $container->connections()->add($entry->id(), $entry);

            $this->logger->debug(\vsprintf('Connection with ID: "%s" has been created', [$entry->id()]));
        }

        return $this;
    }

    /**
     * @param Container $container
     * @param array     $config
     *
     * @return $this
     */
    protected function createTopics(Container $container, array  $config)
    {
        $topics = $config['topics'];

        foreach ($topics as $id => $topic) {
            $topic = \array_merge(
                [
                    'connection' => '',
                    'name'       => '',
                    'config'     => [],
                ],
                $topic
            );

            if (empty($topic['name'])) {
                throw new \RuntimeException(\vsprintf('Topic name is empty!', []));
            }

            if (
                empty($topic['connection']) ||
                !$container->connections()->has($topic['connection'])
            ) {
                throw new \RuntimeException(
                    \vsprintf(
                        'Could not create topic "%s": connection name "%s" is not defined!',
                        [
                            $topic['name'],
                            $topic['connection'],
                        ]
                    )
                );
            }

            $name       = $topic['name'];
            $connection = $container->connections()->get($topic['connection']);
            $params     = $topic['config'];

            $entry = new Topic($id, $name, $connection, $params, $this->logger);

            $container->topics()->add($entry->id(), $entry);

            $this->logger->debug(\vsprintf('Topic with ID: "%s" has been created', [$entry->id()]));
        }//end foreach

        return $this;
    }

    /**
     * @param Container $container
     * @param array     $config
     *
     * @return $this
     */
    protected function createQueues(Container $container, array  $config)
    {
        $queues = $config['queues'];

        foreach ($queues as $id => $queue) {
            $queue = \array_merge(
                [
                    'connection' => '',
                    'name'       => '',
                    'config'     => [],
                ],
                $queue
            );

            if (empty($queue['name'])) {
                throw new \RuntimeException(\vsprintf('Queue name is empty!', []));
            }

            if (
                empty($queue['connection']) ||
                !$container->connections()->has($queue['connection'])
            ) {
                throw new \RuntimeException(
                    \vsprintf(
                        'Could not create queue "%s": connection name "%s" is not defined!',
                        [
                            $queue['name'],
                            $queue['connection'],
                        ]
                    )
                );
            }

            $name       = $queue['name'];
            $connection = $container->connections()->get($queue['connection']);
            $params     = $queue['config'];

            $entry = new Queue($id, $name, $connection, $params, $this->logger);

            $container->queues()->add($entry->id(), $entry);

            $this->logger->debug(\vsprintf('Queue with ID: "%s" has been created', [$entry->id()]));
        }//end foreach

        return $this;
    }

    /**
     * @param Container $container
     * @param array     $config
     *
     * @return $this
     */
    protected function createPublishers(Container $container, array $config)
    {
        $publishers = $config['publishers'];

        foreach ($publishers as $id => $publisher) {
            $publisher = \array_merge(
                [
                    'topic'  => '',
                    'config' => [],
                ],
                $publisher
            );

            if (
                empty($publisher['topic']) ||
                !$container->topics()->has($publisher['topic'])
            ) {
                throw new \RuntimeException(
                    \vsprintf(
                        'Could not create publisher "%s": topic id "%s" is not defined!',
                        [
                            $id,
                            $publisher['topic'],
                        ]
                    )
                );
            }

            $topic  = $container->topics()->get($publisher['topic']);
            $params = $publisher['config'];

            $entry = new Publisher($id, $topic, $container, $params);

            $container->publishers()->add($entry->id(), $entry);

            $this->logger->debug(\vsprintf('Publisher with ID: "%s" has been created', [$entry->id()]));
        }//end foreach

        return $this;
    }

    /**
     * @param Container $container
     * @param array     $config
     *
     * @return $this
     */
    protected function createConsumers(Container $container, array $config)
    {
        $consumers = $config['consumers'];

        foreach ($consumers as $id => $consumer) {
            $consumer = \array_merge(
                [
                    'queue'      => '',
                    'processor'  => null,
                    'config'     => [],
                ],
                $consumer
            );

            if (
                empty($consumer['queue']) ||
                !$container->queues()->has($consumer['queue'])
            ) {
                throw new \RuntimeException(
                    \vsprintf(
                        'Could not create consumer "%s": queue id "%s" is not defined!',
                        [
                            $id,
                            $consumer['queue'],
                        ]
                    )
                );
            }

            $queue     = $container->queues()->get($consumer['queue']);
            $processor = $consumer['processor'];
            $params    = $consumer['config'];

            if (empty($processor)) {
                $processor = new CallbackProcessor(
                    function () {
                        return false;
                    }
                );
            } elseif (\is_callable($processor)) {
                $processor = new CallbackProcessor($processor);
            } elseif (\is_string($processor) && \class_exists($processor)) {
                $processor = new $processor();
            }

            if (!($processor instanceof Processor)) {
                throw new \LogicException(
                    \vsprintf(
                        "Can't create processor, '%s' must extend from %s or its child class.",
                        [
                            \get_class($processor),
                            Processor::class,
                        ]
                    )
                );
            }

            $entry = new Consumer($id, $queue, $processor, $container, $params);

            $container->consumers()->add($entry->id(), $entry);

            $this->logger->debug(\vsprintf('Consumer with ID: "%s" has been created', [$entry->id()]));
        }//end foreach

        return $this;
    }

}
