<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Support\Laravel\Console\Commands;

use BinaryCube\CarrotMQ\Builder\MessageBuilder;

use function feof;
use function fread;
use function vsprintf;

/**
 * Class PublisherCommand
 */
class PublisherCommand extends BaseCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
                            carrot-mq:publish
                            {publisher}
                            {--route= : Routing Key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes a publisher that reads data from STDIN';

    /**
     * Execute the console command.
     *
     * @return integer
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function handleInternal()
    {
        $publisherId = (string) $this->input->getArgument('publisher');
        $route       = (string) $this->input->getOption('route');
        $data        = '';

        if (! $this->carrot->container()->publishers()->has($publisherId)) {
            $this->error(vsprintf('Publisher "%s" not found.', [$publisherId]));
            return 0;
        }

        $publisher = $this->carrot->container()->publishers()->get($publisherId);

        while (! feof(STDIN)) {
            $data .= fread(STDIN, 8192);
        }

        if (empty($data)) {
            return 0;
        }

        $message = MessageBuilder::create()->body($data)->build();

        if (! empty($route)) {
            $message->setRoutingKey($route);
        }

        $publisher->publish($message);

        return 0;
    }

}
