<?php

namespace Sunhill\Files\Console;

use Illuminate\Console\Scheduling\Schedule;
use Sunhill\Files\Processors\Scanner;
use Illuminate\Console\Command;
use Sunhill\Files\Console\CrawlerCommand;

class Scan extends Command
{

    use CrawlerCommand;
    
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'scanner:scan {target} {--s|sync} {--S|no-sync} {--r|recursive} {--R|no-recursive} {--K|skip-duplicates} {--k|no-skip-duplicates} {--P|suppress-source} {--p|no-suppress-source} {--e|erase-empty-dirs} {--E|no-erase-empty-dirs} {--tags=} {--associations=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Scans the given file or directory';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $scanner = new Scanner($this,
            $this->getSync(),
            $this->getRecursive(),
            $this->getSkipDuplicates(),
            $this->getSuppressSource(),
            $this->getOutput()->getVerbosity(),
            $this->getEraseEmptyDirs(),
            $this->getTags(),
            $this->getAssociations());
        $scanner->scan($this->argument("target"));
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
