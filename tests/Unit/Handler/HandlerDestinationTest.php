<?php

namespace Sunhill\Files\Tests\Unit\Handler;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Sunhill\Files\Tests\TestCase;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Handler\HandlerDestination;
use Sunhill\Files\Tests\CrawlerTestCase;
use Sunhill\Basic\Utils\Descriptor;
use Sunhill\Files\Objects\File;

class HandlerDestinationTest extends CrawlerTestCase
{
 
    public function testDestination()
    {
        $descriptor = new CrawlerDescriptor();
        $descriptor->file = new File();
        $descriptor->dbstate = new Descriptor();
        
        $descriptor->file->sha1_hash = 'abc';
        $descriptor->dbstate->wasInDatabase = true;
        $descriptor->file->ext = 'txt';
        
        Config::set("crawler.media_dir",dirname(__FILE__).'/../../temp');
        $test = new HandlerDestination();
        $test->process($descriptor);
        
        $this->assertEquals("/originals/a/b/c/",$descriptor->target->dir);
    }
}