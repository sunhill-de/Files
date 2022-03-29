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

class CompleteScenario extends ScenarioBase
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
            ], 
            'FamilyMember'=>[
                ['firstname','lastname','sex','date_of_birth','father','mother'],
                [
                    'Ned'=>['Ned','Flanders','male','1980-10-01',null,null],
                    'Maude'=>['Maude','Flanders','female','1981-01-01',null,null],
                    'Todd'=>['Todd','Flanders','male','2010-01-10','=>Ned','=>Maude'],
                    'Rod'=>['Rod','Flanders','male','2012-09-10','=>Ned','=>Maude']
                ],
            ],
            'Friend'=>[
                ['firstname','lastname','sex','date_of_birth'],
                [
                    ['Barney','Gumble','male','1976-11-11'],
                    ['Carl','Carlson','male','1978-10-14'],
                    ['Seymor','Skinner','male','1960-05-05']
                ]
            ],
            'Person'=>[
                ['firstname','lastname','sex'],
                [
                    ['Edna','Krabable','female'],
                    ['Rex','Banner','male'],
                    ['Agnes','Skinner','female']
                ]
            ],    
            'Country'=>[
                ['name','iso_code'],
                [
                    'USA'=>['United States','us'],
                    ['Canada','cd'],
                    'Germany'=>['Germany','de']
                ]
            ],
            'City'=>[
                ['name','part_of'],
                [
                    'Springfield'=>['Springfield','=>USA'],
                    'Berlin'=>['Berlin','=>Germany']
                ]
            ],
            'Street'=>[
                ['name','part_of'],
                [
                    'EGT'=>['Evergreen terrace','=>Springfield'],
                    ['Kufürstendamm','=>Berlin']
                ]
            ],
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