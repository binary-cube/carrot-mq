<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Extension;

use BinaryCube\CarrotMQ\Event;

use function vsprintf;
use function microtime;

/**
 * Class IdleExtension
 */
class IdleExtension extends Extension
{

    /**
     * @var integer
     */
    protected $idleTimeout = 0;

    /**
     * @var float
     */
    protected $tick;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            Event\Consumer\Start::name()                => 'onTick',
            Event\Consumer\MessageReceived::name()      => 'onTick',
            Event\Consumer\AfterMessageReceived::name() => 'onTick',
            Event\Consumer\Idle::name()                 => 'onIdle',
        ];
    }

    /**
     * @return string
     */
    public static function name(): string
    {
        return 'IdleExtension';
    }

    /**
     * @return string
     */
    public static function description(): string
    {
        return '';
    }

    /**
     * Constructor.
     *
     * @param int|float $idleTimeout Default 30 seconds
     */
    public function __construct($idleTimeout = 30)
    {
        parent::__construct();

        $this->idleTimeout = $idleTimeout;
        $this->tick        = 0;
    }

    /**
     * @return void
     */
    protected function tick(): void
    {
        $this->tick = microtime(true);
    }

    /**
     * @param Event\Event $event
     *
     * @return void
     */
    public function onTick(Event\Event $event): void
    {
        $this->tick();
    }

    /**
     * @param Event\Consumer\Idle $event
     *
     * @return void
     */
    public function onIdle(Event\Consumer\Idle $event): void
    {
        if (
            false === (
                $this->idleTimeout > 0 &&
                $this->tick > 0 &&
                ((microtime(true) - $this->tick) >= $this->idleTimeout)
            )
        ) {
            return;
        }

        $this
            ->logger
            ->debug(
                vsprintf(
                    '[%s] Interrupt execution. Reached the limit of %s seconds',
                    [
                        self::name(),
                        $this->idleTimeout,
                    ]
                )
            );

        $event->interruptExecution();
    }

}
