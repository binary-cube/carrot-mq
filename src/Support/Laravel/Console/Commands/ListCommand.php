<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Support\Laravel\Console\Commands;

use Symfony\Component\Console\Helper\TableSeparator;

/**
 * Class ListCommand
 */
class ListCommand extends BaseCommand
{

    const
        GROUP_ALL       = 'all',
        GROUP_QUEUES    = 'queues',
        GROUP_TOPICS    = 'topics',
        GROUP_CONSUMERS = 'consumers';

    /**
     * @var array
     */
    protected $allowedGroups = [
        self::GROUP_ALL,
        self::GROUP_QUEUES,
        self::GROUP_TOPICS,
        self::GROUP_CONSUMERS,
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
                            carrot-mq:list
                            {group=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listing container options like: queues, topics, consumers, publishers. Default is `all`.';

    /**
     * Execute the console command.
     *
     * @return integer
     */
    public function handleInternal()
    {
        $group = $this->input->getArgument('group');
        $group = \in_array($group, $this->allowedGroups) ? $group : self::GROUP_ALL;

        $rows = [];

        switch ($group) {
            case self::GROUP_ALL:
                $rows = $this->buildAllRow();
                break;

            case self::GROUP_QUEUES:
                $rows = $this->buildQueuesRows();
                break;

            case self::GROUP_TOPICS:
                $rows = $this->buildTopicsRows();
                break;

            case self::GROUP_CONSUMERS:
                $rows = $this->buildConsumersRows();
                break;
        }

        $this->table(['Group', 'Values'], $rows);

        return 0;
    }

    /**
     * @return array
     */
    protected function buildAllRow()
    {
        $separator = new TableSeparator();

        return (
            \array_merge(
                [],
                $this->buildQueuesRows(),
                [$separator],
                $this->buildTopicsRows(),
                [$separator],
                $this->buildConsumersRows(),
                [$separator],
                $this->buildPublishersRows()
            )
        );
    }

    /**
     * @return array
     */
    protected function buildQueuesRows()
    {
        $result = [];
        $items  = [];

        foreach ($this->carrot->container()->queues()->all() as $name => $queue) {
            $items[] = \vsprintf('%s (%s)', [$name, $queue->name()]);
        }

        $result[] = [
            'queues',
            \implode(PHP_EOL, $items),
        ];

        return $result;
    }

    /**
     * @return array
     */
    protected function buildTopicsRows()
    {
        $result = [];
        $items  = [];

        foreach ($this->carrot->container()->topics()->all() as $name => $topic) {
            $items[] = \vsprintf('%s (%s)', [$name, $topic->name()]);
        }

        $result[] = [
            'topics',
            \implode(PHP_EOL, $items),
        ];

        return $result;
    }

    /**
     * @return array
     */
    protected function buildConsumersRows()
    {
        $result = [];
        $items  = [];

        foreach ($this->carrot->container()->consumers()->all() as $name => $consumer) {
            $items[] = \vsprintf('%s', [$name]);
        }

        $result[] = [
            'consumers',
            \implode(PHP_EOL, $items),
        ];

        return $result;
    }

    /**
     * @return array
     */
    protected function buildPublishersRows()
    {
        $result = [];
        $items  = [];

        foreach ($this->carrot->container()->publishers()->all() as $name => $consumer) {
            $items[] = \vsprintf('%s', [$name]);
        }

        $result[] = [
            'publishers',
            \implode(PHP_EOL, $items),
        ];

        return $result;
    }

}
