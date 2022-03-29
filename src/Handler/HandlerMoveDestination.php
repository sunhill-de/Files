<?php

namespace Sunhill\Files\Handler;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Facades\FileManager;

/**
 * Handles the entries in the database
 * @author klaus
 *
 */
class HandlerMoveDestination extends HandlerBase
{
    
    public static $prio = 50; 
 
    function process(CrawlerDescriptor $descriptor)
    {
        if ($descriptor->isToKeep()) {

            $destination = FileManager::normalizeDir(config('crawler.media_dir').DIRECTORY_SEPARATOR.$descriptor->target->dir)."/".
                            $descriptor->file->sha1_hash.".".$descriptor->file->ext;

            if ($descriptor->alreadyInDatabase() && !$descriptor->keep) {
                unlink($descriptor->source);
                return;
            }

            if (FileManager::fileExists($destination)) {
                $this->error("File '$destination' already exists in originals. Aborting.");
                $descriptor->stop = true;
                return;
            }


            if ($descriptor->keep) {
                FileManager::copyFile($descriptor->source,$destination);
            } else {
                try {
                       FileManager::moveFile($descriptor->source,$destination);
                } catch (\Exception $e) {
                       $this->error("File could not be moved.");
                }
            }

          //  $descriptor->destination = $destination;
        }  else if ($descriptor->keep) {
            unlink($descriptor->source);
        }    
    }
    
    function matches(CrawlerDescriptor $descriptor): Bool
    {
        return $descriptor->fileProcessable() && !$descriptor->stateIs('ignored') && !$descriptor->alreadyInMedia();
    }
    
    
}
