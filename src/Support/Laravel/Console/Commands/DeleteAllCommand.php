<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Support\Laravel\Console\Commands;

use function vsprintf;

/**
 * Class DeleteAllCommand
 */
class DeleteAllCommand extends BaseCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carrot-mq:delete-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all queues and topics';

    /**
     * Execute the console command.
     *
     * @return integer
     */
    public function handleInternal()
    {
        /*
        |--------------------------------------------------------------------------
        | Topics
        |--------------------------------------------------------------------------
        |
        */
        foreach ($this->carrot->container()->topics()->all() as $entity) {
            $entity->delete();

            $this->output->writeln(
                vsprintf(
                    'Deleted TOPIC <info>%s</info> - <fg=yellow>%s</>',
                    [
                        $entity->id(),
                        $entity->name(),
                    ]
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Queues
        |--------------------------------------------------------------------------
        |
        */
        foreach ($this->carrot->container()->queues()->all() as $entity) {
            $entity->delete();

            $this->output->writeln(
                vsprintf(
                    'Deleted QUEUE <info>%s</info> - <fg=yellow>%s</>',
                    [
                        $entity->id(),
                        $entity->name(),
                    ]
                )
            );
        }

        return 0;
    }

}
