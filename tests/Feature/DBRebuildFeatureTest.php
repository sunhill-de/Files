<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Facades\FileManager;
use Sunhill\Files\Handler\HandlerLinks;
use Sunhill\Basic\Tests\SunhillScenarioTestCase;
use Sunhill\Files\Tests\CreatesApplication;
use Sunhill\Files\Tests\Scenarios\ComplexScanScenario;
use Sunhill\Files\Tests\Scenarios\SimpleScanScenario;
use Sunhill\Files\Objects\Dir;
use Sunhill\Files\Objects\File;
use Sunhill\Files\Objects\Link;
use Sunhill\Files\Processors\Scanner;

class DBRebuildFeatureTest extends SunhillScenarioTestCase
{
    
    use CreatesApplication;
    
    protected function GetScenarioClass()
    {
        return ComplexScanScenario::class;
    }
    
    private function cleanDatabase()
    {
        DB::table('objects')->truncate();
        DB::table('fileobjects')->truncate();
        DB::table('files')->truncate();
        DB::table('dirs')->truncate();
        DB::table('links')->truncate();
        DB::table('objectobjectassigns')->truncate();
        DB::table('caching')->truncate();
    }
    
    private function executeCrawler(string $params="")
    {
        Config::set("crawler.media_dir",$this->getTempDir()."/media");
        return $this->artisan("scanner:scan '".$this->getTempDir()."/media'");
    }
    

    public function testSuccessfulExecution()
    {
        $this->cleanDatabase();
        $this->executeCrawler();    
        $this->skipRebuild();
        
        // Must include the file
        $file = File::search()->where('sha1_hash','=','6dcd4ce23d88e2ee9568ba546c007c63d9131c1b')->loadIfExists();
        $this->assertFalse(is_null($file));
        
        // Must insert the originals dir
        $result = Dir::search()->where('full_path','=','originals/6/d/c/')->loadIfExists();
        $this->assertFalse(is_null($result));
        
        // Must insert at least one link
        $result = Link::search()->where('target','=',$file)->loadIfExists();
        $this->assertFalse(is_null($result));

        // Must insert the old source link
        $result = Link::search()->where('name','=','link')->loadIfExists();
        $this->assertFalse(is_null($result));
        $this->assertEquals('6dcd4ce23d88e2ee9568ba546c007c63d9131c1b',$result->target->sha1_hash);
        
        // Mustn't create new source links
        $this->assertFalse(file_exists($this->getTempDir().'/media/sources/all/'.$this->getTempDir().'/originals'));
    }
    
}
