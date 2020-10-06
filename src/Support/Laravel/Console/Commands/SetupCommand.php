<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Support\Laravel\Console\Commands;

use Illuminate\Support\Facades\Log;

/**
 * Class SetupCommand
 */
class SetupCommand extends BaseCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carrot-mq:setup {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all queues and topics';

    /**
     * @return int
     *
     * @throws \Exception
     */
    protected function handleInternal()
    {
        $force = $this->input->getOption('force');

        /*
        |--------------------------------------------------------------------------
        | Topics
        |--------------------------------------------------------------------------
        |
        */
        foreach ($this->carrot->container()->topics()->all() as $entity) {
            if ($force) {
                $entity->delete();

                $this->output->writeln(
                    \vsprintf(
                        'Deleted TOPIC <info>%s</info> - <fg=yellow>%s</>',
                        [
                            $entity->id(),
                            $entity->name(),
                        ]
                    )
                );
            }

            $entity->install();

            $this->output->writeln(
                \vsprintf(
                    'Created <info>TOPIC</info> <fg=yellow>%s</> - <fg=yellow>%s</>',
                    [
                        $entity->name(),
                        $entity->id(),
                    ]
                )
            );
        }//end foreach

        /*
        |--------------------------------------------------------------------------
        | Queues
        |--------------------------------------------------------------------------
        |
        */
        foreach ($this->carrot->container()->queues()->all() as $entity) {
            if ($force) {
                $entity->delete();

                $this->output->writeln(
                    \vsprintf(
                        'Deleted QUEUE <info>%s</info> - <fg=yellow>%s</>',
                        [
                            $entity->id(),
                            $entity->name(),
                        ]
                    )
                );
            }

            $entity->install();

            $this->output->writeln(
                \vsprintf(
                    'Created <info>QUEUE</info> <fg=yellow>%s</> - <fg=yellow>%s</>',
                    [
                        $entity->name(),
                        $entity->id(),
                    ]
                )
            );
        }//end foreach

        return 0;
    }

}
