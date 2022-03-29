<?php

namespace Sunhill\Files\Console;

use Illuminate\Console\Scheduling\Schedule;
use Sunhill\Files\Processors\Eraser;
use Illuminate\Console\Command;
use Sunhill\Files\Console\CrawlerCommand;

class Delete extends Command
{

    use CrawlerCommand;
    
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'scanner:delete {target} {--s|sync} {--S|no-sync} {--r|recursive} {--R|no-recursive} {--e|erase-empty-dirs} {--E|no-erase-empty-dirs}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Marks the given file or directory as deleted';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $scanner = new Eraser($this,
            $this->getSync(),
            $this->getRecursive(),
            $this->getOutput()->getVerbosity(),
            $this->getEraseEmptyDirs());
        $scanner->delete($this->argument("target"));
    }

    protected function getEraseEmptyDirs()
    {
        return $this->getSwitch('erase-empty-dirs','no-erase-empty-dirs',true);
    }
    
    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule)
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
