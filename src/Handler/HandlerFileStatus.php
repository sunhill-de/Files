<?php

namespace Sunhill\Files\Handler;

use Illuminate\Support\Facades\DB;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Facades\FileManager;
use Sunhill\Basic\Utils\Descriptor;

/**
 * Checks if the file exists and if its readable respective writeable
 * @author klaus
 * Depends: none
 * Modifies: 
 * - filestate.exists
 * - filestate.type
 * - filestate.current_location
 * - filestate.readable
 * - filestate.writeable
 * - filestate.sha1_hash
 * - filestate.inMedia
 * - filestate.alreadyInMedia
 * - source
 * Condition: none
 */
class HandlerFileStatus extends HandlerBase
{
 
    public static $prio = 1; // Should run first
    
    function process(CrawlerDescriptor $descriptor)
    {
        if ($descriptor->filestate->exists = FileManager::entryExists($descriptor->getCurrentLocation())) {            
            
            if (is_link($descriptor->getCurrentLocation())) {
                $descriptor->filestate->type = 'link';
                $this->verboseinfo("  Detecting a link");
                
                if ($target = FileManager::normalizeFile(readlink($descriptor->getCurrentLocation()))) {
                    $descriptor->setCurrentLocation($target);                    
                    $this->verboseinfo("  Link points to $target.");
                }                
            } else if (is_file($descriptor->getCurrentLocation())) {
                $descriptor->filestate->type = 'file';
                $this->verboseinfo("  Detecting a file");
            } else {
                $descriptor->filestate->type = 'unknown';
                $this->verboseinfo("  Unknown file type");
            }

            $descriptor->filestate->readable = is_readable($descriptor->getCurrentLocation());
            $descriptor->filestate->writeable = is_writeable($descriptor->getCurrentLocation());
            
            if ($descriptor->filestate->readable = is_readable($descriptor->getCurrentLocation())) {
                $descriptor->filestate->sha1_hash = sha1_file($descriptor->getCurrentLocation());                
                $this->verboseinfo("  Hash is '".$descriptor->filestate->sha1_hash."'");
            }

            if ($descriptor->filestate->inMedia = FileManager::fileInDir($descriptor->getCurrentLocation(),FileManager::getMediaDir())) {
                $descriptor->filestate->alreadyInMedia = true;    
                $this->verboseinfo("  Scanning inside media");
                
            } else {
                $descriptor->filestate->alreadyInMedia = false;
                $this->verboseinfo("  Scanning outside media");
            }
            
        } else {
            $this->verboseinfo("  File does not exist.");
            $descriptor->filestate->readable = false;
            $descriptor->filestate->writeable = false;
            $descriptor->filestate->type = 'unknown';
        }
    }

    function matches(CrawlerDescriptor $descriptor): Bool
    {
        return true; // Every file can be processed
    }
    
}
