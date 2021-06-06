<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Extension;

use BinaryCube\CarrotMQ\Event;

use function vsprintf;

/**
 * Class LimitConsumedMessagesExtension
 */
class LimitConsumedMessagesExtension extends Extension
{

    /**
     * @var integer
     */
    protected $limit;

    /**
     * @var integer
     */
    protected $consumed;

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
            Event\Consumer\AfterMessageReceived::name() => 'onAfterMessageReceived',
        ];
    }

    /**
     * @return string
     */
    public static function name(): string
    {
        return 'LimitConsumedMessagesExtension';
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
     * @param int $limit
     */
    public function __construct(int $limit)
    {
        parent::__construct();

        $this->limit    = $limit;
        $this->consumed = 0;
    }

    /**
     * @param Event\Consumer\AfterMessageReceived $event
     *
     * @return void
     */
    public function onAfterMessageReceived(Event\Consumer\AfterMessageReceived $event): void
    {
        $this->consumed++;

        if ($this->shouldBeStopped()) {
            $event->interruptExecution();
        }
    }

    /**
     * @return boolean
     */
    public function shouldBeStopped(): bool
    {
        if ($this->consumed < $this->limit) {
            return false;
        }

        $this
            ->logger
            ->debug(
                vsprintf(
                    '[%s] Interrupt execution. Reached the limit of %s',
                    [
                        self::name(),
                        $this->limit,
                    ]
                )
            );

        return true;
    }

}
