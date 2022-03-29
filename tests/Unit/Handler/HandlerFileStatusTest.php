<?php

namespace Sunhill\Files\Tests\Unit\Handler;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Sunhill\Files\Tests\TestCase;
use Sunhill\Basic\Tests\SunhillScenarioTestCase;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Handler\HandlerFileStatus;
use Sunhill\Files\Tests\CrawlerTestCase;
use Sunhill\Files\Tests\CreatesApplication;
use Sunhill\Files\Tests\Scenarios\ComplexScanScenario;

class HandlerFileStatusTest extends SunhillScenarioTestCase
{
 
    use CreatesApplication;
    
    protected function GetScenarioClass()
    {
        return ComplexScanScenario::class;
    }
    
    /**
     * @dataProvider FileStatusProvider
     * @param unknown $file
     * @param unknown $field
     * @param unknown $expect
     * @param number $mode
     */
    public function testFileStatus($file,$field,$expect,$mode=0777)
    {
        $source = $this->getTempDir().$file;
        chmod($source,$mode);
        
        $descriptor = new CrawlerDescriptor();
        $descriptor->setSource($source);
        
        Config::set("crawler.media_dir",$this->getTempDir().'media');
        $test = new HandlerFileStatus(null);
        $test->process($descriptor);
        
        $this->assertEquals($expect,$descriptor->filestate->$field);
        
    }
    
    public function FileStatusProvider()
    {
        return [
            ['/scan/A.txt','exists',true],  
            ['/scan/A.txt','readable',true],
            ['/scan/A.txt','writeable',true],
            ['/scan/A.txt','type','file'],
            ['/scan/A.txt','writeable',false,0444],
            ['/scan/A.txt','inMedia',false],
            ['/media/originals/6/d/c/6dcd4ce23d88e2ee9568ba546c007c63d9131c1b.txt','inMedia',true],
            ['/media/source/all/some/dir/link.txt','type','link'],
            ['/media/source/all/some/dir/link.txt','sha1_hash','6dcd4ce23d88e2ee9568ba546c007c63d9131c1b'],
            ['/media/source/all/some/dir/link.txt','inMedia',true],
        ];
    }
    
    public function testUnreadableFile()
    {
        $temp = $this->getTempDir();
        
        $descriptor = new CrawlerDescriptor();
        $descriptor->source = "/etc/shadow"; // Better not run as root
        
        $test = new HandlerFileStatus(null);
        $test->process($descriptor);
        
        $this->assertTrue($descriptor->filestate->exists);
        $this->assertFalse($descriptor->filestate->readable);
        $this->assertFalse($descriptor->filestate->writeable);
        $this->assertEquals('file',$descriptor->filestate->type);
    }
    
}