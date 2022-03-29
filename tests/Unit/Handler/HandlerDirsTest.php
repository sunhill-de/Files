<?php

namespace Sunhill\Files\Tests\Unit\Handler;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Handler\HandlerDirs;
use Sunhill\Basic\Tests\SunhillScenarioTestCase;
use Sunhill\Files\Tests\CreatesApplication;
use Sunhill\Files\Tests\Scenarios\FilesystemScenario;

class HandlerDirsTest extends SunhillScenarioTestCase
{
 
    use CreatesApplication;
    
    public function testDirs()
    {
        $descriptor = new CrawlerDescriptor();
        $descriptor->addDirs = ['/testdir'];        

        Config::set("crawler.media_dir",dirname(__FILE__).'/../../temp');
        $test = new HandlerDirs();
        $test->process($descriptor);
        
        $this->assertTrue(file_exists(dirname(__FILE__).'/../../temp/testdir'));
    }
    
    public function testMoreDirs()
    {
        $descriptor = new CrawlerDescriptor();
        $descriptor->addDirs = ['/testdir/subdir/anothersub'];
        
        Config::set("crawler.media_dir",dirname(__FILE__).'/../../temp');
        $test = new HandlerDirs();
        $test->process($descriptor);
        
        $this->assertTrue(file_exists(dirname(__FILE__).'/../../temp/testdir/subdir/anothersub'));
    }
    
    protected function GetScenarioClass()
    {
        return FilesystemScenario::class;        
    }
    
}