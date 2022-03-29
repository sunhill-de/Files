<?php
namespace Sunhill\Files\Tests\Unit;

use Sunhill\Basic\Tests\SunhillScenarioTestCase;
use Sunhill\Files\Facades\FileManager;
use Sunhill\Files\Managers\FileManagerException;
use Sunhill\Files\Tests\CreatesApplication;
use Sunhill\Files\Tests\Scenarios\FilesystemScenario;
use Illuminate\Support\Facades\Config;

class FileManagerTest extends SunhillScenarioTestCase
{
    
    use CreatesApplication;
    
    protected function GetScenarioClass()
    {
        return FilesystemScenario::class;
    }

    public function setUp(): void
    {
        parent::setUp();
        Config::set("crawler.media_dir",$this->getTempDir());
    }
    
    public function testMediaDir() 
    {
        $this->assertEquals($this->getTempDir(),FileManager::getMediaDir());
    }
    
    /**
     * @dataProvider GetAbolutePathProvider
     */
    public function testGetAbsolutePath($test,$expect) {
        $expect = str_replace('__TEMP__',$this->getTempDir(),$expect);
        $test = str_replace('__TEMP__',$this->getTempDir(),$test);
        $this->assertEquals($expect,FileManager::getAbsolutePath($test));
    }
    
    public function GetAbolutePathProvider()
    {
        return [
            ['__TEMP__test','__TEMP__test'],
            ['test','__TEMP__test'],
        ];
    }
    
    // Tests if an entry exists
    public function testEntryExsists()
    {
        $tmpdir = $this->getTempDir();
        $this->assertTrue(FileManager::entryExists($tmpdir . '/test'));
        $this->assertTrue(FileManager::entryExists($tmpdir . '/test/testa.txt'));
        $this->assertTrue(FileManager::entryExists($tmpdir . '/test/linka'));
        $this->assertFalse(FileManager::entryExists($tmpdir . '/nonexisting'));
    }
    
    // Tests if the directory exsits
    public function testDirectoryExsists()
    {
        $tmpdir = $this->getTempDir();
        $this->assertTrue(FileManager::dirExists($tmpdir . '/test'));
        $this->assertFalse(FileManager::dirExists($tmpdir . '/nonexisting'));
    }
    
    // Tests if directory is readable
    public function testDirectoryReadable()
    {
        $tmpdir = $this->getTempDir();
        $this->assertTrue(FileManager::dirReadable($tmpdir . '/test'));
        $this->assertFalse(FileManager::dirReadable('/root'));
    }
    
    // Tests if directory is writable
    public function testDirectoryWriteable()
    {
        $tmpdir = $this->getTempDir();
        $this->assertTrue(FileManager::dirWritable($tmpdir . '/test'));
        $this->assertFalse(FileManager::dirWritable('/usr'));
    }
    
    // Tests retrieving the subdirectories
    public function testGetSubdirectories()
    {
        $tmpdir = $this->getTempDir();
        $this->assertEquals([
            'a',
            'b',
            'c'
        ], FileManager::getSubdirectories($tmpdir . '/test'));
    }
    
    // Tests retrieving the subdirectories of a non existing dir
    public function testGetSubdirectoriesNonExisting()
    {
        $this->expectException(FileManagerException::class);
        $tmpdir = $this->getTempDir();
        FileManager::getSubdirectories($tmpdir . '/nonexisting');
        $this->expectException(FileManagerException::class);
    }
    
    // Tests retrieving the subdirectories of a non readable dir
    public function testGetSubdirectoriesNonReadable()
    {
        $this->expectException(FileManagerException::class);
        $tmpdir = $this->getTempDir();
        $this->assertEquals([
            'a',
            'b',
            'c'
        ], FileManager::getSubdirectories('/root'));
    }
    
    // Tests retrieving the files
    public function testGetFiles()
    {
        $tmpdir = $this->getTempDir();
        $this->assertEquals([
            'testa.txt',
            'testb.txt'
        ], FileManager::getFiles($tmpdir . '/test'));
    }
    
    // Tests retrieving the links
    public function testGetLinks()
    {
        $tmpdir = $this->getTempDir();
        $this->assertEquals([
            'linka'
        ], FileManager::getLinks($tmpdir . '/test'));
    }
    
    // Tests retrieving all entries
    public function testGetEntries()
    {
        $tmpdir = $this->getTempDir();
        $this->assertEquals([
            'a' => 'dir',
            'b' => 'dir',
            'c' => 'dir',
            'testa.txt' => 'file',
            'testb.txt' => 'file',
            'linka' => 'link'
        ], FileManager::getEntries($tmpdir . '/test'));
    }
    
    // Tests retrieving all entries with grouping
    public function testGetEntriesWithGroup()
    {
        $tmpdir = $this->getTempDir();
        $this->assertEquals([
            'dirs' => [
                'a',
                'b',
                'c'
            ],
            'files' => [
                'testa.txt',
                'testb.txt'
            ],
            'links' => [
                'linka'
            ]
        ], FileManager::getEntries($tmpdir . '/test', true));
    }
    
    // Tests if a file is in a dir
    public function testFileInDir()
    {
        $tmpdir = $this->getTempDir();
        $this->assertTrue(FileManager::fileInDir($tmpdir . '/test/testa.txt', $tmpdir . '/test'),'File_in_dir 1');
        $this->assertTrue(FileManager::fileInDir($tmpdir . '/test/testa.txt', $tmpdir . '/test/'),'File_in_dir 2');
        $this->assertFalse(FileManager::fileInDir($tmpdir . '/media/testa.txt', $tmpdir . '/test'),'File_in_dir 3');
    }
    
    // Tests if a dir is in a dir
    public function testDirInDir()
    {
        $tmpdir = $this->getTempDir();
        $this->assertTrue(FileManager::dirInDir($tmpdir . '/test/a', $tmpdir . '/test'));
        $this->assertTrue(FileManager::dirInDir($tmpdir . '/test/a/', $tmpdir . '/test/'));
        $this->assertFalse(FileManager::dirInDir($tmpdir . '/media/', $tmpdir . '/test'));
    }
    
    /**
     *
     * @dataProvider RenameDirProvider
     */
    public function testRenameDir(string $source, string $dest, string $additional_pos, string $additional_neg)
    {
        $source = str_replace('__TEMP__',$this->getTempDir(),$source);
        $dest = str_replace('__TEMP__',$this->getTempDir(),$dest);
        $additional_pos = str_replace('__TEMP__',$this->getTempDir(),$additional_pos);
        $additional_neg = str_replace('__TEMP__',$this->getTempDir(),$additional_neg);
        FileManager::renameDir($source, $dest);
        $this->assertTrue(file_exists($dest));
        $this->assertFalse(file_exists($source));
        if (! empty($additional_pos)) {
            $this->assertTrue(file_exists($additional_pos));
        }
        if (! empty($additional_neg)) {
            $this->assertFalse(file_exists($additional_neg));
        }
    }
    
    public function RenameDirProvider()
    {
        return [
            [
                '__TEMP__test/a',
                '__TEMP__test/aa',
                '',
                ''
            ],
            [
                '__TEMP__test/c',
                '__TEMP__test/cc',
                '__TEMP__test/cc/d',
                ''
            ],
            [
                '__TEMP__test/c',
                '__TEMP__tust/c',
                '__TEMP__tust/c/d',
                ''
            ],
            [
                '__TEMP__test/c/d',
                '__TEMP__test/cc/d',
                '__TEMP__test/cc',
                '__TEMP__test/c'
            ],
            [
                '__TEMP__test/c/d',
                '__TEMP__test/z',
                '',
                '__TEMP__test/c'
            ],
            [
                '__TEMP__test/c/d',
                '__TEMP__test/y/z',
                '',
                '__TEMP__test/c'
            ]
        ];
    }
    
    public function testRenameDirBothSame()
    {
        $tmpdir = $this->getTempDir();
        FileManager::renameDir($tmpdir . '/test/c/d', $tmpdir . '/test/c/d');
        $this->assertTrue(file_exists($tmpdir . '/test/c/d'));
    }
    
    public function testEraseDir()
    {
        $tmpdir = $this->getTempDir();
        FileManager::eraseDir($tmpdir . '/test/c/d');
        $this->assertFalse(file_exists($tmpdir . '/test/c/d'));
    }
    
    public function testEraseNotEmptyDir() {
        $this->expectException(FileManagerException::class);
        $tmpdir = $this->getTempDir();
        FileManager::eraseDir($tmpdir . '/test/c');
        $this->assertFalse(file_exists($tmpdir . '/test/c'));        
    }
    
    public function testEraseNotEmptyDirRecursive() {
        $tmpdir = $this->getTempDir();
        FileManager::eraseDir($tmpdir . '/test/c',true);
        $this->assertFalse(file_exists($tmpdir . '/test/c'));
    }
    
    public function testCreateDir() {
        $tmpdir = $this->getTempDir();
        FileManager::createDir($tmpdir . '/test/c/newdir');
        $this->assertTrue(file_exists($tmpdir . '/test/c/newdir'));        
    }
    
    /**
     *
     * @dataProvider EffectiveDirProvider
     * @return string
     */
    public function testEffectiveDir($test, $expect)
    {
        $this->assertEquals($expect, FileManager::normalizeDir($test));
    }
    
    public function EffectiveDirProvider()
    {
        return [
            [
                'a//b',
                'a/b/'
            ],
            [
                'a/../b',
                'b/'
            ],
            [
                '/a/b/c/./../../d/',
                '/a/d/'
            ]
        ];
    }
    
    /**
     *
     * @dataProvider GetRelativeProvider
     * @param unknown $source
     * @param unknown $target
     * @param unknown $expect
     */
    public function testGetRelativeDir($source, $target, $expect)
    {
        $this->assertEquals($expect, FileManager::getRelativeDir($source, $target));
    }
    
    public function GetRelativeProvider()
    {
        return [
            [
                'a/b/c/',
                'a/b/c/d/',
                'd/'
            ],
            [
                'a/b/c/',
                'a/b/',
                '../'
            ],
            [
                'a/b/c/',
                'a/',
                '../../'
            ],
            [
                'a/b/c/',
                'a/b/d/',
                '../d/'
            ],
            [
                'a/b/c/',
                'a/d/e/',
                '../../d/e/'
            ],
            [
                'a/b/',
                'a/c/d/e/f/',
                '../c/d/e/f/'
            ],
            [
                'a/b/c/d/e/f/',
                'a/g/',
                '../../../../../g/'
            ]
        ];
    }
    
    public function testDirectoryEmpty()
    {
        $this->assertTrue(FileManager::dirEmpty($this->getTempDir().'/test/b'));        
        $this->assertFalse(FileManager::dirEmpty($this->getTempDir().'/subdir'));        
        $this->assertFalse(FileManager::dirEmpty($this->getTempDir().'/test/a'));        
    }

    // Tests if a link exists
    public function testLinkExists()
    {
        $tmpdir = $this->getTempDir();
        $this->assertTrue(FileManager::linkExists($tmpdir . '/test/linka'));
        $this->assertFalse(FileManager::linkExists($tmpdir . '/test/nonexisting'));
    }
    
    // Tests if a links points to an existing target
    public function testLinkTargetExists()
    {
        $tmpdir = $this->getTempDir();
        $this->assertTrue(FileManager::linkExists($tmpdir . '/test/linka'));
        $this->assertFalse(FileManager::linkExists($tmpdir . '/test/a/linka'));
    }
    
    // Tests if a link is relative or absolute
    public function testLinkIsRelative()
    {
        $tmpdir = $this->getTempDir();
        $this->assertTrue(FileManager::linkIsRelative($tmpdir . '/test/a/linkb'));
        $this->assertFalse(FileManager::linkIsRelative($tmpdir . '/test/linka'));
    }
    
    // Tests removing a link
    public function testRemoveLink()
    {
        $tmpdir = $this->getTempDir();
        FileManager::removeLink($tmpdir . '/test/linka');
        $this->assertFalse(file_exists($tmpdir . '/test/linka'));
    }
    
    // Tests creating a link
    public function testCreateLink()
    {
        $tmpdir = FileManager::normalizeDir($this->getTempDir());
        FileManager::createLink($tmpdir.'test/linknew', $tmpdir.'test/testa.txt');
        $this->assertEquals('TestA', file_get_contents($tmpdir . 'test/linknew'));
    }
    
    // Tests creating a link with non existing target
    public function testCreateLink_nonexisting_target()
    {
        $this->expectException(FileManagerException::class);
        $tmpdir = $this->getTempDir();
        FileManager::createLink($tmpdir . '/test/linknew', $tmpdir . '/test/nonexisting.txt');
    }
    
    // Tests creating a link
    public function testCreateLinkRelative()
    {
        $tmpdir = $this->getTempDir();
        FileManager::createLink($tmpdir . '/test/a/linknew', '../testa.txt');
        $this->assertEquals('TestA', file_get_contents($tmpdir . '/test/a/linknew'));
    }
    
    // Tests creating a link
    public function testCreateLinkRelative_nonexisting_target()
    {
        $this->expectException(FileManagerException::class);
        $tmpdir = $this->getTempDir();
        FileManager::createLink($tmpdir . '/test/a/linknew', '../nonexisting.txt');
    }
    
    // Tests if file exists
    public function testFileExists()
    {
        $tmpdir = $this->getTempDir();
        $this->assertTrue(FileManager::fileExists($tmpdir . '/test/testa.txt'));
        $this->assertFalse(FileManager::fileExists($tmpdir . '/test/nonexisting.txt'));
        $this->assertFalse(FileManager::fileExists($tmpdir . '/test/a'));
        $this->assertFalse(FileManager::fileExists($tmpdir . '/test/linka'));
    }
    
    // Tests if file is readable
    public function testFileReadable()
    {
        $tmpdir = $this->getTempDir();
        $this->assertTrue(FileManager::fileReadable($tmpdir . '/test/testa.txt'));
        $this->assertFalse(FileManager::fileReadable('/etc/shadow'));
    }
    
    // Tests if file is writable
    public function testFileWritable()
    {
        $tmpdir = $this->getTempDir();
        $this->assertTrue(FileManager::fileWritable($tmpdir . '/test/testa.txt'));
        $this->assertFalse(FileManager::fileWritable('/etc/passwd'));
    }
    
    // Tests deleting of a file
    public function testDeleteFile()
    {
        $tmpdir = $this->getTempDir();
        FileManager::deleteFile($tmpdir . '/test/testa.txt');
        $this->assertFalse(file_exists($tmpdir . '/test/testa.txt'));
    }
    
    // Tests copying of a file
    public function testCopyFile()
    {
        $tmpdir = $this->getTempDir();
        FileManager::copyFile($tmpdir . '/test/testa.txt', $tmpdir . '/test/filecopy.txt');
        $this->assertTrue(file_exists($tmpdir . '/test/testa.txt'));
        $this->assertTrue(file_exists($tmpdir . '/test/filecopy.txt'));
    }
    
    // Tests copying of a file with existing target
    public function testCopyFile_destexisting()
    {
        $this->expectException(FileManagerException::class);
        $tmpdir = $this->getTempDir();
        FileManager::copyFile($tmpdir . '/test/testa.txt', $tmpdir . '/test/testb.txt');
    }
    
    // Tests copying of a file with missing source
    public function testCopyFile_sourcemissing()
    {
        $this->expectException(FileManagerException::class);
        $tmpdir = $this->getTempDir();
        FileManager::copyFile($tmpdir . '/test/nonexisting.txt', $tmpdir . '/test/filecopy.txt');
    }
    
    // Tests moving of a file
    public function testMoveFile()
    {
        $tmpdir = $this->getTempDir();
        FileManager::moveFile($tmpdir . '/test/testa.txt', $tmpdir . '/test/filecopy.txt');
        $this->assertFalse(file_exists($tmpdir . '/test/testa.txt'));
        $this->assertTrue(file_exists($tmpdir . '/test/filecopy.txt'));
    }
    
    // Tests moving of a file with existing target
    public function testMoveFile_TargetExisting()
    {
        $this->expectException(FileManagerException::class);
        $tmpdir = $this->getTempDir();
        FileManager::moveFile($tmpdir . '/test/testa.txt', $tmpdir . '/test/testb.txt');
    }
    
    // Tests moving of a file with missing source
    public function testMoveFile_SourceMissing()
    {
        $this->expectException(FileManagerException::class);
        $tmpdir = $this->getTempDir();
        FileManager::moveFile($tmpdir . '/test/nonexisting.txt', $tmpdir . '/test/filecopy.txt');
    }
    
    // Test if two files are equal
    public function testFilesEqual()
    {
        $tmpdir = $this->getTempDir();
        exec("cp $tmpdir/test/testa.txt $tmpdir/test/filecopy.txt");
        $this->assertTrue(FileManager::filesEqual($tmpdir . '/test/testa.txt', $tmpdir . '/test/filecopy.txt'));
        $this->assertFalse(FileManager::filesEqual($tmpdir . '/test/testa.txt', $tmpdir . '/test/testb.txt'));
    }
    
}
