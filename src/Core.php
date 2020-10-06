<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ;

use Psr\Log\LoggerInterface;

/**
 * Class Core
 */
abstract class Core extends Component
{

    /**
     * @var Container
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param string               $id
     * @param Container            $container
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $id, Container $container, $logger = null)
    {
        parent::__construct($id, $logger);

        $this->container = $container;
    }

    /**
     * @return Container
     */
    public function container()
    {
        return $this->container;
    }

}
