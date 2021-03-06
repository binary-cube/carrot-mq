<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Entity;

use Interop\Amqp\AmqpTopic;
use Psr\Log\LoggerInterface;
use Interop\Amqp\Impl\AmqpBind;
use BinaryCube\CarrotMQ\Connection;
use BinaryCube\CarrotMQ\Support\Collection;
use BinaryCube\CarrotMQ\Exception\Exception;

use function vsprintf;
use function filter_var;
use function array_reduce;
use function array_filter;
use function array_intersect_key;

/**
 * Class Topic
 */
final class Topic extends Entity
{

    const
        TYPE_DIRECT  = AmqpTopic::TYPE_DIRECT,
        TYPE_FANOUT  = AmqpTopic::TYPE_FANOUT,
        TYPE_TOPIC   = AmqpTopic::TYPE_TOPIC,
        TYPE_HEADERS = AmqpTopic::TYPE_HEADERS;

    /**
     * @const array Default exchange parameters
     */
    const DEFAULTS = [
        'type'                         => self::TYPE_DIRECT,

        'passive'                      => false,
        'durable'                      => true,
        'auto_delete'                  => false,
        'internal'                     => false,
        'nowait'                       => false,

        'arguments'                    => [],

        'bind'                         => [],

        'auto_create'                  => true,

        'throw_exception_on_redeclare' => true,
        'throw_exception_on_bind_fail' => true,
    ];

    /**
     * Constructor.
     *
     * @param string               $id
     * @param string               $name
     * @param Connection           $connection
     * @param array                $config
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        string $id,
        string $name,
        Connection $connection,
        array $config = [],
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($id, $name, $connection, $config, $logger);

        $this->config = Collection::make(static::DEFAULTS)->merge($config)->all();
    }

    /**
     * @return AmqpTopic
     */
    public function model(): AmqpTopic
    {
        return $this->context()->createTopic($this->name());
    }

    /**
     * @return $this
     *
     * @throws Exception
     */
    public function create()
    {
        $exchange = $this->model();

        $properties = [
            'passive'    => AmqpTopic::FLAG_PASSIVE,
            'durable'    => AmqpTopic::FLAG_DURABLE,
            'autoDelete' => AmqpTopic::FLAG_AUTODELETE,
            'internal'   => AmqpTopic::FLAG_INTERNAL,
            'nowait'     => AmqpTopic::FLAG_NOWAIT,
        ];

        $flags = array_reduce(
            array_intersect_key(
                $properties,
                array_filter(
                    $this->config,
                    function ($value) {
                        return $value === true;
                    }
                )
            ),
            function ($a, $b) {
                return ($a | $b);
            },
            (AmqpTopic::FLAG_NOPARAM)
        );

        $exchange->setType($this->config['type']);
        $exchange->setFlags($flags);

        if (! empty($this->config['arguments'])) {
            $exchange->setArguments($this->config['arguments']);
        }

        try {
            $this->context()->declareTopic($exchange);
        } catch (\Exception $exception) {
            if (true === $this->config['throw_exception_on_redeclare']) {
                throw new Exception($exception->getMessage(), $exception->getCode());
            }
        }

        $this->logger->debug(vsprintf('Topic "%s" ("%s") has been created', [$this->id(), $this->name()]));

        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        $this->context()->deleteTopic($this->model());

        $this->logger->debug(vsprintf('Topic "%s" ("%s") has been deleted', [$this->id(), $this->name()]));

        return $this;
    }

    /**
     * @return $this
     *
     * @throws Exception
     */
    public function bind()
    {
        if (empty($this->config['bind'])) {
            return $this;
        }

        $default = [
            'queue'       => '',
            'topic'       => '',
            'routing_key' => '',
        ];

        foreach ($this->config['bind'] as $bind) {
            try {
                $bind = Collection::make($default)->merge($bind)->all();

                if (! empty($bind['queue'])) {
                    $queue     = $this->context()->createQueue($bind['queue']);
                    $queueBind = new AmqpBind($this->model(), $queue, $bind['routing_key']);

                    $this->context()->bind($queueBind);
                }

                if (! empty($bind['topic'])) {
                    $topic        = $this->context()->createTopic($bind['topic']);
                    $exchangeBind = new AmqpBind($this->model(), $topic, $bind['routing_key']);

                    $this->context()->bind($exchangeBind);
                }
            } catch (\Exception $exception) {
                if (true === $this->config['throw_exception_on_bind_fail']) {
                    throw new Exception($exception->getMessage(), $exception->getCode());
                }
            }//end try
        }//end foreach

        $this->logger->debug(vsprintf('Setup Topic Binds for "%s" - "%s"', [$this->id(), $this->name()]));

        return $this;
    }

    /**
     * @return boolean
     */
    public function exists(): bool
    {
        $result = false;

        try {
            $exchange = $this->model();

            $exchange->setFlags(AmqpTopic::FLAG_PASSIVE);

            $this->context()->declareTopic($exchange);

            $result = true;
        } catch (\Exception $exception) {
            // Do nothing.
        }

        return $result;
    }

    /**
     * @return $this
     */
    public function purge()
    {
        return $this;
    }

    /**
     * @return boolean
     */
    public function canAutoCreate(): bool
    {
        return filter_var($this->config['auto_create'], FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return $this
     *
     * @throws Exception
     */
    public function install()
    {
        $this->create()->bind();

        return $this;
    }

}
