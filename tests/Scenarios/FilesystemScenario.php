<?php

namespace Sunhill\Files\Tests\Scenarios;

use Sunhill\Basic\Tests\Scenario\ScenarioBase;
use Sunhill\Basic\Tests\Scenario\ScenarioWithDirs;
use Sunhill\Basic\Tests\Scenario\ScenarioWithFiles;
use Sunhill\Basic\Tests\Scenario\ScenarioWithLinks;

class FilesystemScenario extends ScenarioBase
{
    use ScenarioWithDirs,ScenarioWithFiles,ScenarioWithLinks;
    
    protected $Requirements = [
        'Dirs'=>[
            'destructive'=>true,
            ],
        'Files'=>[
            'destructive'=>true,
            ],
        'Links'=>[
            'destructive'=>true,            
            ]    
    ];
    
    protected function getDirs()
    {
        return [
            '/subdir/',
            '/subdir/subsubdir/',
            '/test',            
            '/test/a',
            '/test/b',
            '/test/c',
            '/test/c/d',
        ];
    }
    
    protected function getFiles()
    {
        return [
            ['path'=>'/A.txt','content'=>'A'],
            ['path'=>'/B.txt','content'=>'B'],
            ['path'=>'/C.txt','content'=>'C'],
            ['path'=>'/D.txt','content'=>'D'],
            ['path'=>'/subdir/A.txt','content'=>'A'],
            ['path'=>'/test/testa.txt','content'=>'TestA'],
            ['path'=>'/test/testb.txt','content'=>'TestB'],
            ['path'=>'/test/c/testa.txt','content'=>'TestA'],
        ];    
    }
    
    protected function getLinks()
    {
        return [
            ['link'=>'/test/linka','target'=>'/test/testa.txt'],
            ['link'=>'/test/a/linka','target'=>'/test/nonexisting.txt'],
            ['link'=>'/test/a/linkb','target'=>'../testa.txt'],
        ];
    }
    
    public function __construct()
    {
        $this->setTarget(storage_path('temp/'));
        exec("rm -rf ".storage_path('temp/').'*');
    }
}