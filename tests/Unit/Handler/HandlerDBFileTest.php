<?php

namespace Sunhill\Files\Tests\Unit\Handler;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Handler\HandlerDBFile;
use Sunhill\Files\Tests\CrawlerTestCase;
use Sunhill\Basic\Utils\Descriptor;
use Sunhill\Files\Objects\File;

class HandlerDBFileTest extends CrawlerTestCase
{
 
    public function testInsertion()
    {
        $descriptor = new CrawlerDescriptor();
        $descriptor->file = new File();
        $descriptor->dbstate = new Descriptor();
        
        $descriptor->file->sha1_hash = 'ABC';
        $descriptor->file->ext = 'txt';
        $descriptor->file->size = 10;
        $descriptor->file->created = mktime(10,11,12,2,1,2003);
        $descriptor->file->changed = mktime(10,11,12,2,1,2003);
        
        DB::table("files")->truncate();
        
        $test = new HandlerDBFile();
        $test->process($descriptor);
        
        $this->assertDatabaseCount("files",1);
        $this->assertDatabaseHas("files",["sha1_hash"=>"ABC","ext"=>"txt","size"=>10,"created"=>"2003-02-01 10:11:12","changed"=>"2003-02-01 10:11:12"]);
        
    }
}