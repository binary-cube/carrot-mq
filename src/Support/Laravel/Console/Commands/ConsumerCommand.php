<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Support\Laravel\Console\Commands;

use BinaryCube\CarrotMQ\Consumer;
use BinaryCube\CarrotMQ\Extension;
use Illuminate\Support\Facades\Log;

/**
 * Class ConsumerCommand
 */
class ConsumerCommand extends BaseCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
                            carrot-mq:consume
                            {consumer}
                            {--message-limit=0 : Message Limit}
                            {--idle-timeout=30 : Close gracefully when there are no incoming messages in the given time interval.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start consuming messages';

    /**
     * Execute the console command.
     *
     * @return integer
     *
     * @throws \Exception
     */
    public function handleInternal()
    {
        $consumerId = (string) $this->input->getArgument('consumer');

        if (! $this->carrot->container()->consumers()->has($consumerId)) {
            $this->error(\vsprintf('Consumer "%s" not found.', [$consumerId]));
            return 0;
        }

        /* @var Consumer $consumer */
        $consumer = $this->carrot->container()->consumers()->get($consumerId);

        $this->registerExtensions($consumer);

        $consumer->consume();

        return 0;
    }

    /**
     * @param Consumer $consumer
     *
     * @return void
     */
    protected function registerExtensions(Consumer $consumer)
    {
        $messageLimit = \intval($this->input->getOption('message-limit'));
        $idleTimeout  = \intval($this->input->getOption('idle-timeout'));

        $extensions = [
            new Extension\SignalExtension(),
            new Extension\IdleExtension($idleTimeout),
        ];

        if ($messageLimit > 0) {
            $extensions[] = new Extension\LimitConsumedMessagesExtension($messageLimit);
        }

        foreach ($extensions as $extension) {
            /**
             * @var Extension\Extension $extension
             */
            $consumer->extensions()->put($extension::name(), $extension);
        }
    }

}
