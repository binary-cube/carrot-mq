<?php

namespace BinaryCube\CarrotMQ\Entity;

use Psr\Log\LoggerInterface;
use BinaryCube\CarrotMQ\Component;
use BinaryCube\CarrotMQ\Connection;

/**
 * Class Entity
 */
abstract class Entity extends Component
{

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
    public function __construct(string $id, string $name, Connection $connection, $config = [], $logger = null)
    {
        parent::__construct($id, $logger);

        $this->id         = $id;
        $this->name       = $name;
        $this->connection = $connection;
        $this->config     = $config;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return Connection
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * @return array
     */
    public function config()
    {
        return $this->config;
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
    abstract public function exists();

    /**
     * @return $this
     */
    abstract public function install();

    /**
     * @return boolean
     */
    abstract public function canAutoCreate();

}
