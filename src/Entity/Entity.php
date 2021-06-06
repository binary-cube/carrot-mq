<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Entity;

use Psr\Log\LoggerInterface;
use Interop\Amqp\AmqpContext;
use BinaryCube\CarrotMQ\Component;
use BinaryCube\CarrotMQ\Connection;
use BinaryCube\CarrotMQ\Support\LoggerAwareTrait;

/**
 * Class Entity
 */
abstract class Entity extends Component
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $config;

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
        parent::__construct($id, $logger);

        $this->id         = $id;
        $this->name       = $name;
        $this->connection = $connection;
        $this->config     = $config;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return Connection
     */
    public function connection(): Connection
    {
        return $this->connection;
    }

    /**
     * @return array
     */
    public function config(): array
    {
        return $this->config;
    }

    /**
     * @param bool $refresh
     *
     * @return AmqpContext
     */
    public function context(bool $refresh = false): AmqpContext
    {
        return $this->connection->context($refresh);
    }

    /**
     * @return mixed
     */
    abstract public function model();

    /**
     * @return $this
     */
    abstract protected function create();

    /**
     * @return $this
     */
    abstract public function delete();

    /**
     * @return $this
     */
    abstract protected function bind();

    /**
     * @return boolean
     */
    abstract public function exists(): bool;

    /**
     * @return $this
     */
    abstract public function install();

    /**
     * @return boolean
     */
    abstract public function canAutoCreate(): bool;

    /**
     * @return $this
     */
    public function reconnect()
    {
        $this->connection->reconnect();

        return $this;
    }

}
