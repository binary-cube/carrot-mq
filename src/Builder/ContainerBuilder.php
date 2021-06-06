<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Builder;

use Psr\Log\LoggerInterface;
use BinaryCube\CarrotMQ\Config;
use BinaryCube\CarrotMQ\Consumer;
use BinaryCube\CarrotMQ\Publisher;
use BinaryCube\CarrotMQ\Component;
use BinaryCube\CarrotMQ\Container;
use BinaryCube\CarrotMQ\Connection;
use BinaryCube\CarrotMQ\Entity\Queue;
use BinaryCube\CarrotMQ\Entity\Topic;
use BinaryCube\CarrotMQ\Support\Collection;
use BinaryCube\CarrotMQ\Processor\Processor;
use BinaryCube\CarrotMQ\Processor\CallbackProcessor;

use function vsprintf;
use function is_string;
use function get_class;
use function is_callable;
use function class_exists;

/**
 * Class ContainerBuilder
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
     * @param Config               $config
     * @param LoggerInterface|null $logger
     *
     * @return Container
     */
    public static function create(Config $config, ?LoggerInterface $logger = null)
    {
        $builder = new static($logger);

        return $builder->build($config);
    }

    /**
     * Constructor.
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        parent::__construct(null, $logger);
    }

    /**
     * @param Config $config
     *
     * @return Container
     */
    public function build(Config $config): Container
    {
        $config = Collection::make(static::DEFAULTS)->merge($config->all())->all();

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
    protected function createConnections(Container $container, array $config)
    {
        $connections = $config['connections'];

        foreach ($connections as $id => $connection) {
            $entry = new Connection($id, $connection, $this->logger);

            $container->connections()->put($entry->id(), $entry);

            $this->logger->debug(vsprintf('Connection with ID: "%s" has been created', [$entry->id()]));
        }

        return $this;
    }

    /**
     * @param Container $container
     * @param array     $config
     *
     * @return $this
     */
    protected function createTopics(Container $container, array $config)
    {
        $topics = $config['topics'];

        $default = [
            'connection' => '',
            'name'       => '',
            'config'     => [],
        ];

        foreach ($topics as $id => $topic) {
            $topic = Collection::make($default)->merge($topic)->all();

            if (empty($topic['name'])) {
                throw new \RuntimeException(vsprintf('Topic name is empty!', []));
            }

            if (
                empty($topic['connection']) ||
                ! $container->connections()->has($topic['connection'])
            ) {
                throw new \RuntimeException(
                    vsprintf(
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

            $container->topics()->put($entry->id(), $entry);

            $this->logger->debug(vsprintf('Topic with ID: "%s" has been created', [$entry->id()]));
        }//end foreach

        return $this;
    }

    /**
     * @param Container $container
     * @param array     $config
     *
     * @return $this
     */
    protected function createQueues(Container $container, array $config)
    {
        $queues = $config['queues'];

        $default = [
            'connection' => '',
            'name'       => '',
            'config'     => [],
        ];

        foreach ($queues as $id => $queue) {
            $queue = Collection::make($default)->merge($queue)->all();

            if (empty($queue['name'])) {
                throw new \RuntimeException(vsprintf('Queue name is empty!', []));
            }

            if (
                empty($queue['connection']) ||
                ! $container->connections()->has($queue['connection'])
            ) {
                throw new \RuntimeException(
                    vsprintf(
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

            $container->queues()->put($entry->id(), $entry);

            $this->logger->debug(vsprintf('Queue with ID: "%s" has been created', [$entry->id()]));
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

        $default = [
            'topic'  => '',
            'config' => [],
        ];

        foreach ($publishers as $id => $publisher) {
            $publisher = Collection::make($default)->merge($publisher)->all();

            if (
                empty($publisher['topic']) ||
                ! $container->topics()->has($publisher['topic'])
            ) {
                throw new \RuntimeException(
                    vsprintf(
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

            $entry = new Publisher($id, $topic, $container, $params, $this->logger);

            $container->publishers()->put($entry->id(), $entry);

            $this->logger->debug(vsprintf('Publisher with ID: "%s" has been created', [$entry->id()]));
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

        $default = [
            'queue'      => '',
            'processor'  => null,
            'config'     => [],
        ];

        foreach ($consumers as $id => $consumer) {
            $consumer = Collection::make($default)->merge($consumer)->all();

            if (
                empty($consumer['queue']) ||
                ! $container->queues()->has($consumer['queue'])
            ) {
                throw new \RuntimeException(
                    vsprintf(
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
            } elseif (is_callable($processor)) {
                $processor = new CallbackProcessor($processor);
            } elseif (is_string($processor) && class_exists($processor)) {
                $processor = new $processor();
            }

            if (! ($processor instanceof Processor)) {
                throw new \LogicException(
                    vsprintf(
                        "Can't create processor, '%s' must extend from %s or its child class.",
                        [
                            get_class($processor),
                            Processor::class,
                        ]
                    )
                );
            }

            $entry = new Consumer($id, $queue, $processor, $container, $params, $this->logger);

            $container->consumers()->put($entry->id(), $entry);

            $this->logger->debug(vsprintf('Consumer with ID: "%s" has been created', [$entry->id()]));
        }//end foreach

        return $this;
    }

}
