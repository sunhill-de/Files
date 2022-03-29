<?php

namespace Sunhill\Files\Handler;


use Illuminate\Support\Facades\DB;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Facades\FileManager;
use Sunhill\Files\Facades\FileObjects;
use Sunhill\Files\Objects\Link;

/**
 * Handles the entries in the database
 * @author klaus
 *
 */
class HandlerLinks extends HandlerBase
{
    
    public static $prio = 55;

    function process(CrawlerDescriptor $descriptor)
    {
        if (count($descriptor->addLinks)) {
            $this->addLinks($descriptor);
        }
        if (count($descriptor->removeLinks)) {
            $this->removeLinks($descriptor);
        }
    }

    private function addLinks($descriptor)
    {
        foreach ($descriptor->addLinks as $link) {
            $this->addFSLink($descriptor,$link);
            $this->addDBLink($descriptor,$link);
        }            
    }
    
    private function addFSLink($descriptor,$link)
    {
        $destination = FileManager::normalizeFile(config('crawler.media_dir')."/".$link);
        $target = FileManager::normalizeFile(config('crawler.media_dir')."/".$descriptor->target->path);       
        $destination_dir = pathinfo($destination,PATHINFO_DIRNAME);
        $target_dir = pathinfo($target,PATHINFO_DIRNAME);
        $relative = FileManager::getRelativeDir($destination_dir,$target_dir);
        $relative_target = $relative.pathinfo($target,PATHINFO_BASENAME);
        
        if (!FileManager::dirExists($destination_dir)) {
            throw new \Exception("The expected dir '$destination_dir' does not exist.");
        }
        if (FileManager::LinkExists($destination)) {
            $this->error("The destination '$destination' already exists.");
            return;
        } 
        if (!FileManager::fileExists($destination_dir.'/'.$relative_target)) {
            $this->error("The target '".$destination_dir.'/'.$relative_target."' does not exist.");
        } else {
            FileManager::createLink($destination,$relative_target);
        }        
    }
    
    private function addDBLink($descriptor,$link)
    {
        $link_obj = new Link();
        $link_obj->name = pathinfo($link,PATHINFO_FILENAME);
        $link_obj->ext = pathinfo($link,PATHINFO_EXTENSION);
        $link_obj->parent_dir = FileObjects::searchDir(pathinfo($link,PATHINFO_DIRNAME));
        $link_obj->target = $descriptor->file;
        $link_obj->commit();
    }
    
    private function removeLinks($descriptor)
    {
        
    }
    
    function matches(CrawlerDescriptor $descriptor): Bool
    {
        return $descriptor->fileReadable();
    }
    
    
}
