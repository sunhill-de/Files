<?php

namespace Sunhill\Files\Tests\Unit\Handler;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Sunhill\Files\Tests\TestCase;
use Sunhill\Basic\Tests\SunhillScenarioTestCase;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Handler\HandlerFileObject;
use Sunhill\Files\Tests\CrawlerTestCase;
use Sunhill\Files\Tests\CreatesApplication;
use Sunhill\Files\Tests\Scenarios\ComplexScanScenario;

class HandlerFileObjectTest extends SunhillScenarioTestCase
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
    public function testFileStatus($file,$descriptorinfo,$group,$field,$expect)
    {
        if ($file[0] == '/') {
            $source = $this->getTempDir().$file;
        } else {
            $source = dirname(__FILE__).'/'.$file;    
        }
        
        $descriptor = new CrawlerDescriptor();
        $descriptor->filestate->exists = true;
        
        foreach ($descriptorinfo as $key => $value) {
            if (strpos($key,'.')) {
                list($main,$sub) = explode('.',$key);
                $descriptor->$main->$sub = $value;
            } else {
                $descripor->$key = $value;
            }
        }
        
        $descriptor->setSource($source);
        
        Config::set("crawler.media_dir",$this->getTempDir().'media');
        $test = new HandlerFileObject(null);
        $test->process($descriptor);
        
        if (strpos($field,'.')) {
            list($key1,$key2) = explode('.',$field);
            $this->assertEquals($expect,$descriptor->$group->$key1->$key2);
        } else {
            $this->assertEquals($expect,$descriptor->$group->$field);
        }
        
    }
    
    public function FileStatusProvider()
    {
        return [
            [
                '/scan/A.txt',
                [
                    'filestate.sha1_hash'=>'6dcd4ce23d88e2ee9568ba546c007c63d9131c1b'                    
                ],
                'dbstate','isInDatabase',true,                
            ],  
            [
                '/scan/A.txt',
                [
                    'filestate.sha1_hash'=>'6dcd4ce23d88e2ee9568ba546c007c63d9131c1b'
                ],
                'dbstate','wasInDatabase',true,
            ],
            [
                '/scan/A.txt',
                [
                    'filestate.sha1_hash'=>'6dcd4ce23d88e2ee9568ba546c007c63d9131c1b'
                ],
                'file','sha1_hash','6dcd4ce23d88e2ee9568ba546c007c63d9131c1b',
            ],
            [
                '/scan/A.txt',
                [
                    'filestate.sha1_hash'=>'6dcd4ce23d88e2ee9568ba546c007c63d9131c1b'
                ],
                'file','type','regular',
            ],
            [
                '/scan/A.txt',
                [
                    'filestate.sha1_hash'=>'6dcd4ce23d88e2ee9568ba546c007c63d9131c1b'
                ],
                'filestate','mime_str','application/octet-stream',
            ],
            [
                '/scan/B.txt',
                [
                    'filestate.sha1_hash'=>'ae4f281df5a5d0ff3cad6371f76d5c29b6d953ec'
                ],
                'dbstate','isInDatabase',false,
            ],
            [
                '/scan/B.txt',
                [
                    'filestate.sha1_hash'=>'ae4f281df5a5d0ff3cad6371f76d5c29b6d953ec'
                ],
                'dbstate','wasInDatabase',false,
            ],
            [
                '/scan/B.txt',
                [
                    'filestate.sha1_hash'=>'ae4f281df5a5d0ff3cad6371f76d5c29b6d953ec'
                ],
                'file','mime.mime','application/octet-stream',
            ],
            
            [
                '../../files/testfiles/audio-flac/test.flac',
                [
                    'filestate.sha1_hash'=>'ae4f281df5a5d0ff3cad6371f76d5c29b6d953ec'
                ],
                'filestate','mime_str','audio/flac',
            ],
            [
                '../../files/testfiles/audio-mp3/test.mp3',
                [
                    'filestate.sha1_hash'=>'ae4f281df5a5d0ff3cad6371f76d5c29b6d953ec'
                ],
                'filestate','mime_str','audio/mpeg',
            ],
            [
                '../../files/testfiles/image-heif/test.heic',
                [
                    'filestate.sha1_hash'=>'ae4f281df5a5d0ff3cad6371f76d5c29b6d953ec'
                ],
                'filestate','mime_str','image/heic',
            ],
            [
                '../../files/testfiles/image-jpeg/test.jpg',
                [
                    'filestate.sha1_hash'=>'ae4f281df5a5d0ff3cad6371f76d5c29b6d953ec'
                ],
                'filestate','mime_str','image/jpeg',
            ],
        ];
    }
    
 }