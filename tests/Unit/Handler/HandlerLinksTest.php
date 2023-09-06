<?php

namespace Sunhill\Files\Tests\Unit\Handler;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Facades\FileObjects;
use Sunhill\Files\Handler\HandlerLinks;
use Sunhill\Basic\Tests\SunhillScenarioTestCase;
use Sunhill\Files\Tests\CreatesApplication;
use Sunhill\Files\Tests\Scenarios\ComplexScanScenario;
use Sunhill\Basic\Utils\Descriptor;
use Sunhill\Files\Objects\File;
use Sunhill\ORM\Facades\Objects;

class HandlerLinksTest extends SunhillScenarioTestCase
{
    
    protected function GetScenarioClass()
    {
        return ComplexScanScenario::class;
    }
    
    public function testDummy()
    {
        $descriptor = new CrawlerDescriptor();
        $descriptor->file = new File();
        $descriptor->target = new Descriptor();
        
        FileObjects::searchOrInsertDir('/source/');
        $descriptor->target->path = 'originals/6/d/c/6dcd4ce23d88e2ee9568ba546c007c63d9131c1b.txt';
        $descriptor->addLinks = ['/source/a.txt'];
        $descriptor->removeLinks = [];
        $descriptor->file = File::search()->where('sha1_hash','=','6dcd4ce23d88e2ee9568ba546c007c63d9131c1b')->loadIfExists();
        
        Config::set("crawler.media_dir",$this->getTempDir().'media/');
        $test = new HandlerLinks();
        $test->process($descriptor);

        $this->assertTrue(file_exists($this->getTempDir().'/media/source/a.txt'));
        $this->assertEquals("A",file_get_contents($this->getTempDir().'/media/source/a.txt'));
    }
    
}