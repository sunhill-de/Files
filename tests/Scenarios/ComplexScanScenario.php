<?php

namespace Sunhill\Files\Tests\Scenarios;

use Sunhill\Basic\Tests\Scenario\ScenarioBase;
use Sunhill\Basic\Tests\Scenario\ScenarioWithDirs;
use Sunhill\Basic\Tests\Scenario\ScenarioWithFiles;
use Sunhill\Basic\Tests\Scenario\ScenarioWithTables;
use Sunhill\Basic\Tests\Scenario\ScenarioWithLinks;
use Sunhill\Files\Objects\Dir;
use Sunhill\Files\Objects\File;
use Sunhill\Files\Objects\FileObject;
use Sunhill\Files\Objects\Link;
use Sunhill\Files\Objects\Mime;
use Sunhill\ORM\Facades\Classes;
use Sunhill\ORM\Tests\Scenario\ScenarioWithObjects;
use Sunhill\ORM\Tests\Scenario\ScenarioWithRegistration;

class ComplexScanScenario extends ScenarioBase
{
    use ScenarioWithDirs,ScenarioWithFiles,ScenarioWithLinks,ScenarioWithObjects; 
    
    protected $Requirements = [
        'Dirs'=>[
            'destructive'=>true,
        ],
        'Files'=>[
            'destructive'=>true,
        ],
        'Links'=>[
            'destructive'=>true,
        ],
        'Objects'=>[
            'destructive'=>true,
        ],
        'Registration'=>[
            'destructive'=>true,
        ]
    ];
    
    protected function getDirs()
    {
        return [
            '/media/',
            '/media/originals/',
            '/media/originals/6/',
            '/media/originals/6/d/',
            '/media/originals/6/d/c/',
            '/media/source/',
            '/media/source/all/',
            '/media/source/all/some/',
            '/media/source/all/some/dir/',
            '/scan/',
            '/scan/subdir/'
        ];
    }
    
    protected function getFiles()
    {
        return [
            ['path'=>'/scan/A.txt','content'=>'A'],
            ['path'=>'/scan/B.txt','content'=>'B'],
            ['path'=>'/scan/C.TXT','content'=>'C'],
            ['path'=>'/scan/D.TXT','content'=>'D'],
            ['path'=>'/scan/subdir/AnotherA.txt','content'=>'A'],
            ['path'=>'/media/originals/6/d/c/6dcd4ce23d88e2ee9568ba546c007c63d9131c1b.txt','content'=>'A']
        ];    
    }
    
    protected function getLinks()
    {
        return [
            ['link'=>'/media/source/all/some/dir/link.txt','target'=>'/media/originals/6/d/c/6dcd4ce23d88e2ee9568ba546c007c63d9131c1b.txt'],
        ];    
    }
    
    function GetObjects() {
        return [
            'Mime'=>[
                ['mimegroup','item'],
                [
                    'mime'=>['application','octet-stream']
                ]
            ],
            'Dir'=>[
                ['name','parent_dir'],
                [
                    'originals'=>['originals',null],
                    'd6'=>['6','=>originals'],
                    'dd'=>['d','=>d6'],
                    'dc'=>['c','=>dd'],
                ]
            ],
            'File'=>[
                ['sha1_hash','ext','size','mime','created','changed','parent_dir','type','name'],
                [
                    'file'=>['6dcd4ce23d88e2ee9568ba546c007c63d9131c1b','txt',1,'=>mime','2022-02-11 00:00:00','2022-02-11 00:00:00','=>dc','regular','OldA'],
                ]
            ],
            'Link'=>[
                ['file','parent_dir','name'],
                [
                       
                ]
            ]
            /*'sources'=>[
                ['file_id','source','host'],
                [
                    [1,'/some/source','somehost']
                ]
            ]*/
        ]; 
    }
    
    public function __construct()
    {
        $this->setTarget(storage_path('temp/'));
        exec("rm -rf ".storage_path('temp/').'*');
    }
}