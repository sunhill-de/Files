<?php

namespace Sunhill\Files\Tests\Unit\Handler;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Sunhill\Files\Tests\TestCase;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Handler\HandlerSource;
use Sunhill\Files\Tests\CrawlerTestCase;

class HandlerSourceTest extends CrawlerTestCase
{
 
    public function testSource()
    {
        $descriptor = new CrawlerDescriptor();
        $descriptor->source = '/some/dir/test.txt';
        $descriptor->fileID = 1;
        $descriptor->addLinks = [];
        $descriptor->addDirs = [];
        
        $test = new HandlerSource();
        $test->process($descriptor);
        
        $this->assertEquals("/sources/all/some/dir/test.txt",$descriptor->addLinks[0]);
    }
}