<?php

namespace BinaryCube\CarrotMQ;

use Psr\Log\LoggerInterface;
use BinaryCube\CarrotMQ\Builder\ContainerBuilder;

/**
 * Class CarrotMQ
 *
 * @package BinaryCube\CarrotMQ
 */
class CarrotMQ extends Component
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param array                $config
     * @param LoggerInterface|null $logger
     */
    public function __construct($config = [], LoggerInterface $logger = null)
    {
        parent::__construct(null, $logger);

        $this->config    = $config;
        $this->container = ContainerBuilder::create($this->config, $this->logger);

        $this->logger->debug(\vsprintf('Instance of "%s" has been created', [self::class]));
    }

    /**
     * @return Container
     */
    public function container()
    {
        return $this->container;
    }

}
