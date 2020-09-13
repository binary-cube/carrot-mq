<?php

namespace BinaryCube\CarrotMQ\Support\Laravel\Console\Commands;

/**
 * Class PurgeCommand
 */
class PurgeCommand extends BaseCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carrot-mq:purge {queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all messages from the queue';

    /**
     * Execute the console command.
     *
     * @return integer
     *
     * @throws \Exception
     */
    public function handleInternal()
    {
        $queueId = (string) $this->input->getArgument('queue');

        if (! $this->carrot->container()->queues()->has($queueId)) {
            $this->error(\vsprintf('Queue "%s" not found.', [$queueId]));
            return 0;
        }

        $queue = $this->carrot->container()->queues()->get($queueId);

        $queue->purge();

        return 0;
    }

}
