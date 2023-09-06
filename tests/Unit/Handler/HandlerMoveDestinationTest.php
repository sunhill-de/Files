<?php

namespace Sunhill\Files\Tests\Unit\Handler;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Handler\HandlerMoveDestination;
use Sunhill\Basic\Tests\SunhillScenarioTestCase;
use Sunhill\Files\Tests\CreatesApplication;
use Sunhill\Files\Tests\Scenarios\FilesystemScenario;
use Sunhill\Basic\Utils\Descriptor;
use Sunhill\Files\Objects\File;

class HandlerMoveDestinationTest extends SunhillScenarioTestCase
{
    
    
    public function testCopyFile()
    {
        $descriptor = new CrawlerDescriptor();
        $descriptor->file = new File();
        $descriptor->target = new Descriptor();
        $descriptor->dbstate = new Descriptor();
        
        $descriptor->source = $this->getTempDir().'A.txt';
        $descriptor->target->dir = '/subdir/';
        $descriptor->file->type = 'regular';
        $descriptor->file->sha1_hash = 'abc';
        $descriptor->file->ext = 'txt';
        $descriptor->keep = true;
        $descriptor->filestate->writeable = true;
        $descriptor->dbstate->wasInDatabase = false;
        
        Config::set("crawler.media_dir",$this->getTempDir());
        $test = new HandlerMoveDestination();
        $test->process($descriptor);
        
        $this->assertTrue(file_exists($this->getTempDir().'subdir/abc.txt'));        
        $this->assertTrue(file_exists($this->getTempDir().'A.txt'));
    }
    
    public function testMoveFile()
    {
        $descriptor = new CrawlerDescriptor();
        $descriptor->file = new File();
        $descriptor->target = new Descriptor();
        $descriptor->dbstate = new Descriptor();
        
        $descriptor->source = $this->getTempDir().'A.txt';
        $descriptor->target->dir = '/subdir/';
        $descriptor->file->sha1_hash = 'abc';
        $descriptor->file->ext = 'txt';
        $descriptor->file->type = 'regular';
        $descriptor->keep = false;
        $descriptor->filestate->writeable = true;
        $descriptor->dbstate->wasInDatabase = false;
        Config::set("crawler.media_dir",$this->getTempDir());
        $test = new HandlerMoveDestination();
        $test->process($descriptor);
        
        $this->assertTrue(file_exists($this->getTempDir().'subdir/abc.txt'));
        $this->assertFalse(file_exists($this->getTempDir().'A.txt'));
    }
    
    public function testMoveFileUnwriteable()
    {
        chmod($this->getTempDir().'A.txt',0444);
        $descriptor = new CrawlerDescriptor();
        $descriptor->file = new File();
        $descriptor->target = new Descriptor();
        $descriptor->dbstate = new Descriptor();
        
        $descriptor->source = $this->getTempDir().'A.txt';
        $descriptor->target->dir = 'subdir/';
        $descriptor->file->sha1_hash = 'abc';
        $descriptor->file->type = 'regular';
        $descriptor->file->ext = 'txt';
        $descriptor->keep = false;
        $descriptor->filestate->writeable = false;
        $descriptor->dbstate->wasInDatabase = false;
        
        Config::set("crawler.media_dir",$this->getTempDir());
        $test = new HandlerMoveDestination();
        $test->process($descriptor);
        
        $this->assertTrue(file_exists($this->getTempDir().'subdir/abc.txt'));
        $this->assertFalse(file_exists($this->getTempDir().'A.txt'));
    }
    
    protected function GetScenarioClass()
    {
        return FilesystemScenario::class;
    }
        
}