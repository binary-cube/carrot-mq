<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Entity;

use Psr\Log\LoggerInterface;
use Interop\Amqp\AmqpQueue;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\Impl\AmqpBind;
use BinaryCube\CarrotMQ\Connection;
use BinaryCube\CarrotMQ\Support\Collection;
use BinaryCube\CarrotMQ\Exception\Exception;

use function vsprintf;
use function filter_var;
use function array_filter;
use function array_reduce;
use function array_intersect_key;

/**
 * Class Queue
 */
final class Queue extends Entity
{

    /**
     * @const array Default queue parameters
     */
    const DEFAULTS = [
        'passive'                      => false,
        'durable'                      => false,
        'exclusive'                    => false,
        'auto_delete'                  => false,
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
     * @return AmqpQueue
     */
    public function model(): AmqpQueue
    {
        return $this->context()->createQueue($this->name());
    }

    /**
     * @return $this
     *
     * @throws Exception
     */
    public function create()
    {
        $queue = $this->model();

        $properties = [
            'passive'     => AmqpQueue::FLAG_PASSIVE,
            'durable'     => AmqpQueue::FLAG_DURABLE,
            'exclusive'   => AmqpQueue::FLAG_EXCLUSIVE,
            'auto_delete' => AmqpQueue::FLAG_AUTODELETE,
            'nowait'      => AmqpQueue::FLAG_NOWAIT,
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
            (AmqpQueue::FLAG_NOPARAM)
        );

        $queue->setFlags($flags);

        if (! empty($this->config['arguments'])) {
            $queue->setArguments($this->config['arguments']);
        }

        try {
            $this->context()->declareQueue($queue);
        } catch (\Exception $exception) {
            if (true === $this->config['throw_exception_on_redeclare']) {
                throw new Exception($exception->getMessage(), $exception->getCode());
            }
        }

        $this->logger->debug(vsprintf('Queue "%s" ("%s") has been created', [$this->id(), $this->name()]));

        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        $this->context()->deleteQueue($this->model());

        $this->logger->debug(vsprintf('Queue "%s" ("%s") has been deleted', [$this->id(), $this->name()]));

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
            'topic'       => '',
            'routing_key' => '',
        ];

        foreach ($this->config['bind'] as $bind) {
            try {
                $bind = Collection::make($default)->merge($bind)->all();

                if (empty($bind['topic'])) {
                    return $this;
                }

                $exchange = $this->context()->createTopic($bind['topic']);
                $bind     = new AmqpBind($this->model(), $exchange, $bind['routing_key']);

                $this->context()->bind($bind);
            } catch (\Exception $exception) {
                if (true === $this->config['throw_exception_on_bind_fail']) {
                    throw new Exception($exception->getMessage(), $exception->getCode());
                }
            }//end try
        }//end foreach

        $this->logger->debug(vsprintf('Setup Queue Binds for "%s" ("%s")', [$this->id(), $this->name()]));

        return $this;
    }

    /**
     * @return boolean
     */
    public function exists(): bool
    {
        $result = false;

        try {
            $queue = $this->model();

            $queue->setFlags(AmqpQueue::FLAG_PASSIVE);

            $this->context()->deleteQueue($queue);

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
        try {
            $this->context()->purgeQueue($this->model());
        } catch (\Exception $exception) {
            // Do nothing.
        }

        $this->logger->debug(vsprintf('Queue "%s"("%s") has been purged', [$this->id(), $this->name()]));

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
