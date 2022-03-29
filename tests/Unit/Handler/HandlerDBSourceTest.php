<?php

namespace Sunhill\Files\Tests\Unit\Handler;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Handler\HandlerDBSource;
use Sunhill\Files\Tests\CrawlerTestCase;
use Sunhill\Basic\Utils\Descriptor;

class HandlerDBSourceTest extends CrawlerTestCase
{
 
    public function testSource()
    {
        DB::table("sources")->truncate();
        $descriptor = new CrawlerDescriptor();
        $descriptor->file = new Descriptor();
        $descriptor->source = '/some/dir/test.txt';
        $descriptor->file->ID = 1;
        $descriptor->addLinks = [];
        $descriptor->addDirs = [];
        
        $test = new HandlerDBSource();
        $test->process($descriptor);
        
        $this->assertDatabaseHas("sources",['file_id'=>1,'source'=>'/some/dir/test.txt']);
    }
}