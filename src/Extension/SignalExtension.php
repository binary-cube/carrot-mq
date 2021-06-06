<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Extension;

use BinaryCube\CarrotMQ\Event;
use BinaryCube\CarrotMQ\Exception\Exception;

use function vsprintf;
use function pcntl_signal;
use function extension_loaded;
use function pcntl_async_signals;

/**
 * Class SignalExtension
 */
class SignalExtension extends Extension
{

    /**
     * @var boolean
     */
    protected $interruptConsumption = false;

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
            Event\Consumer\Start::name()                => 'onStart',
            Event\Consumer\AfterMessageReceived::name() => 'onAfterMessageReceived',
            Event\Consumer\Idle::name()                 => 'onIdle',
        ];
    }

    /**
     * @return string
     */
    public static function name(): string
    {
        return 'SignalExtension';
    }

    /**
     * @return string
     */
    public static function description(): string
    {
        return '';
    }

    /**
     * @param Event\Consumer\Start $event
     *
     * @return void
     *
     * @throws Exception
     */
    public function onStart(Event\Consumer\Start $event): void
    {
        if (! extension_loaded('pcntl')) {
            throw new Exception('The pcntl extension is required in order to catch signals.');
        }

        pcntl_async_signals(true);

        foreach ([SIGTERM, SIGINT, SIGHUP, SIGQUIT] as $signal) {
            pcntl_signal($signal, [$this, 'handleSignal']);
        }

        $this->interruptConsumption = false;
    }

    /**
     * @param Event\Consumer\AfterMessageReceived $event
     *
     * @return void
     */
    public function onAfterMessageReceived(Event\Consumer\AfterMessageReceived $event): void
    {
        if ($this->shouldBeStopped()) {
            $event->interruptExecution();
        }
    }

    /**
     * @param Event\Consumer\Idle $event
     *
     * @return void
     */
    public function onIdle(Event\Consumer\Idle $event): void
    {
        if ($this->shouldBeStopped()) {
            $event->interruptExecution();
        }
    }

    /**
     * @param int $signal
     *
     * @return $this
     */
    public function handleSignal($signal)
    {
        $this
            ->logger
            ->debug(
                vsprintf('[%s] Caught signal: %s', [self::name(), $signal])
            );

        /*
        |--------------------------------------------------------------------------
        | Signal Map.
        |--------------------------------------------------------------------------
        | SIGTERM :: supervisor default stop
        | SIGQUIT :: kill -s QUIT
        | SIGINT  :: ctrl+c
        | SIGHUP  :: terminal is closed
        */

        switch ($signal) {
            case SIGTERM:
            case SIGQUIT:
            case SIGINT:
            case SIGHUP:
                $this
                    ->logger
                    ->debug(vsprintf('[%s] Interrupt consumption', [self::name()]));

                $this->interruptConsumption = true;

                break;

            default:
                break;
        }//end switch

        return $this;
    }

    /**
     * @return boolean
     */
    public function shouldBeStopped(): bool
    {
        if (false === $this->interruptConsumption) {
            return false;
        }

        $this
            ->logger
            ->debug(vsprintf('[%s] Interrupt execution', [self::name()]));

        $this->interruptConsumption = false;

        return true;
    }

}
