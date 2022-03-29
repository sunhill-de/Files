<?php

namespace Sunhill\Files\Tests\Unit\Handler;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Handler\HandlerBase;
use Sunhill\Files\Tests\CrawlerTestCase;

class DummyHandler extends HandlerBase
{
    
    public $matches = true;
    
    function process(CrawlerDescriptor $descriptor)
    {
    }
    
    function matches(CrawlerDescriptor $descriptor): Bool
    {
    }

    function pub_addDir(CrawlerDescriptor $descriptor,string $path)
    {
        $this->addDir($descriptor,$path);
    }
    
    function pub_addLink(CrawlerDescriptor $descriptor,string $info, $filename = null)
    {
        $this->addLink($descriptor,$info,$filename);
    }
}

class HandlerBaseTest extends CrawlerTestCase
{
 
    public function testAddDir()
    {
        $descriptor = new CrawlerDescriptor();
        $descriptor->addLinks = [];
        $descriptor->addDirs = [];
 
        $test = new DummyHandler();
        $test->pub_addDir($descriptor, "/test/dir/");
        
        $this->assertEquals(1,count($descriptor->addDirs));
        $this->assertEquals("/test/dir/",$descriptor->addDirs[0]);
    }
    
    public function testAddLinkCombined()
    {
        $descriptor = new CrawlerDescriptor();
        $descriptor->addLinks = [];
        $descriptor->addDirs = [];
        
        $test = new DummyHandler();
        $test->pub_addLink($descriptor, "/test/dir/", "test.txt");
        
        $this->assertEquals(1,count($descriptor->addDirs));
        $this->assertEquals("/test/dir/",$descriptor->addDirs[0]);
        $this->assertEquals(1,count($descriptor->addLinks));
        $this->assertEquals("/test/dir/test.txt",$descriptor->addLinks[0]);
    }
    
    public function testAddLinkCombinedNoTrailingSlash()
    {
        $descriptor = new CrawlerDescriptor();
        $descriptor->addLinks = [];
        $descriptor->addDirs = [];
        
        $test = new DummyHandler();
        $test->pub_addLink($descriptor, "/test/dir", "test.txt");
        
        $this->assertEquals(1,count($descriptor->addDirs));
        $this->assertEquals("/test/dir/",$descriptor->addDirs[0]);
        $this->assertEquals(1,count($descriptor->addLinks));
        $this->assertEquals("/test/dir/test.txt",$descriptor->addLinks[0]);
    }
    
    public function testAddLinkSingle()
    {
        $descriptor = new CrawlerDescriptor();
        $descriptor->addLinks = [];
        $descriptor->addDirs = [];
        
        $test = new DummyHandler();
        $test->pub_addLink($descriptor, "/test/dir/test.txt");
        
        $this->assertEquals(1,count($descriptor->addDirs));
        $this->assertEquals("/test/dir/",$descriptor->addDirs[0]);
        $this->assertEquals(1,count($descriptor->addLinks));
        $this->assertEquals("/test/dir/test.txt",$descriptor->addLinks[0]);
    }
    
    
}