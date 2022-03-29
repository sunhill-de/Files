<?php

namespace Sunhill\Files\Handler;


use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Facades\FileManager;
use Sunhill\Files\Facades\FileObjects;
use Sunhill\Files\Objects\Dir;

/**
 * Handles the entries in the database
 * @author klaus
 *
 */
class HandlerDirs extends HandlerBase
{
    
    public static $prio = 49;

    function process(CrawlerDescriptor $descriptor)
    {
        if (count($descriptor->addDirs)>0) {
            $this->addDirs($descriptor);
        }
    }

    private function addDirs(CrawlerDescriptor $descriptor) 
    {
        foreach ($descriptor->addDirs as $dir) {
            $this->doAddDir($dir);
        }
    }
    
    private function doAddDir(string $dir) 
    {
        $media_path = FileObjects::normalizeMediaPath($dir);
        $full_path = Str::finish(FileManager::normalizeDir(config('crawler.media_dir').DIRECTORY_SEPARATOR.$dir),DIRECTORY_SEPARATOR);

        if (file_exists($full_path)) {
            $this->debug("The dir '$full_path' already exists. Nothing to do.");           
        } else {
            $this->debug("The dir '$full_path' doesn't exist. Creating it.");            
            $this->createDir($media_path,$full_path);
        }
    }

    /**
     * Creates the dir on the disc and adds it to the database
     * @param string $media_path
     * @param string $full_path
     */
    private function createDir(string $media_path, string $full_path)
    {
        $parts = explode(DIRECTORY_SEPARATOR,$media_path);
        array_pop($parts); // ignore trailing slash
        $dir = array_pop($parts);        
        $parent_path = Str::finish(implode(DIRECTORY_SEPARATOR,$parts),DIRECTORY_SEPARATOR);
        
        $this->doAddDir($parent_path);
        // At this point is:
        //  $dir the name of the directory
        //  $parent_path the path of the parent directory
        //  $full_path the full path of the directory (parent_path + dir)
        //  The parent directory created an in the database
        if ($this->createFSDir($full_path)) {
            $this->createDBDir($parent_path, $dir);
        }
    }
    
    private function createFSDir($path)
    {
        try {
            FileManager::createDir($path);
        } catch (\Exception $e) {
            $this->error("Failure while creating '$path'");
        }
        if (!file_exists($path)) {
            $this->error("Couldn't create the target directory '$path'");
            return false;            
        }
        return true;
    }
    
    private function createDBDir($parent,$name)
    {
        $dir = new Dir();
        $dir->parent_dir = FileObjects::searchOrInsertDir($parent);
        $dir->name = $name;
    /*    $dir->fileobject_exists = 1;
        $dir->fileobject_created = true; */
        $dir->commit();
    }
    
    function matches(CrawlerDescriptor $descriptor): Bool
    {
        return $descriptor->fileProcessable();
    }
    
    
}
