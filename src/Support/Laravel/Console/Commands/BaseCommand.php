<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Support\Laravel\Console\Commands;

use Illuminate\Console\Command;
use BinaryCube\CarrotMQ\CarrotMQ;
use Illuminate\Support\Facades\Log;

/**
 * Class BaseCommand
 */
abstract class BaseCommand extends Command
{

    /**
     * @var CarrotMQ
     */
    protected $carrot;

    /**
     * Constructor.
     *
     * @param CarrotMQ $carrot
     */
    public function __construct(CarrotMQ $carrot)
    {
        $this->carrot = $carrot;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return integer
     */
    public function handle()
    {
        try {
            return $this->handleInternal();
        } catch (\Exception $exception) {
            $this->error('Something went wrong! Check the log for more information.');
            Log::error((string) $exception);
        }

        return 0;
    }

    /**
     * @return integer
     */
    abstract protected function handleInternal();

}
