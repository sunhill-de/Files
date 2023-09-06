<?php
namespace Sunhill\Files\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Sunhill\Basic\Tests\SunhillScenarioTestCase;
use Sunhill\Files\Facades\FileObjects;
use Sunhill\Files\Tests\CreatesApplication;
use Sunhill\Files\Tests\Scenarios\FilesystemScenario;

use Sunhill\Files\Objects\Dir;

class FileObjectsTest extends SunhillScenarioTestCase
{
    
    protected function GetScenarioClass()
    {
        return FilesystemScenario::class;
    }

    /**
     * @dataProvider normalizeMediaDirProvider
     * @param unknown $dir
     * @param unknown $expect
     */
    public function testNormalizeMediaDir($dir,$expect)
    {
        Config::set("crawler.media_dir","/media/dir/");
        $this->assertEquals($expect,FileObjects::normalizeMediaPath($dir));
    }
    
    public function normalizeMediaDirProvider() 
    {
        return [
            ['some/dir','some/dir/'],
            ['/some/dir/','some/dir/'],
            ['/some/dir','some/dir/'],
            ['/media/dir/some/dir/','some/dir/'],
            ['media/dir/some/dir','some/dir/'],
            ['/media/dir//some/../dir','dir/']
        ];   
    }
    
    private function cleanTables()
    {
        DB::table('objects')->truncate();
        DB::table('fileobjects')->truncate();
        DB::table('dirs')->truncate();
        DB::table('caching')->truncate();        
        DB::table('objectobjectassigns')->truncate();
    }
    
    public function testSearchDirFail()
    {
        $this->cleanTables();
        $this->assertNull(FileObjects::searchDir('/non/existing'));        
    }
    
    public function testSearchDirPass()
    {
        $this->cleanTables();
        
        $dir = new Dir();
        $dir->parent_dir = null;
        $dir->name = 'dir';
        $dir->commit();
        $this->assertEquals('dir/',FileObjects::searchDir('/dir')->full_path);
    }
    
    public function testSearchOrInsert()
    {
        $this->cleanTables();
        
        
        $dir1 = FileObjects::searchOrInsertDir('/some/dir');
        $dir2 = FileObjects::searchOrInsertDir('/some/otherdir');
        $this->assertEquals($dir1->parent_dir->id,$dir2->parent_dir->id);
    }
}
