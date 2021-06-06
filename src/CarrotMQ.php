<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ;

use Psr\Log\LoggerInterface;
use BinaryCube\CarrotMQ\Builder\ContainerBuilder;

use function vsprintf;

/**
 * Class CarrotMQ
 */
class CarrotMQ extends Component
{

    /**
     * @var Config
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

        $this->config = Config::make($config);

        $this->logger->debug(vsprintf('Instance of "%s" has been created', [static::class]));
    }

    /**
     * @return Container
     */
    public function container(): Container
    {
        if (! isset($this->container)) {
            $this->build();
        }

        return $this->container;
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function reload($config = [])
    {
        $this->config = Config::make($config);

        $this->build();

        return $this;
    }

    /**
     * @return $this
     */
    protected function build()
    {
        $this->container = ContainerBuilder::create($this->config, $this->logger);

        return $this;
    }

}
