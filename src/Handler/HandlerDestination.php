<?php

namespace Sunhill\Files\Handler;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Facades\FileManager;
use Sunhill\Basic\Utils\Descriptor;

/**
 * Handles the entries in the database
 * @author klaus
 *
 */
class HandlerDestination extends HandlerBase
{
    
    public static $prio = 10;

    function process(CrawlerDescriptor $descriptor)
    {
        if (!$descriptor->isDefined('target')) {
            $descriptor->target = new Descriptor();
        }
        $hash = $descriptor->file->sha1_hash;
        
        $descriptor->target->dir = FileManager::normalizeDir("/originals/".
                                  $hash[0]."/".
                                  $hash[1]."/".
                                  $hash[2]."/");
        
        $descriptor->target->path = $descriptor->target->dir.$descriptor->file->sha1_hash.'.'.$descriptor->file->ext;
        if (!$descriptor->alreadyInDatabase()) {
          $descriptor->addDirs[] = $descriptor->target->dir;
        }
        $this->debug("Target dir is calulated to '".$descriptor->target->dir."'");
    }
    
    function matches(CrawlerDescriptor $descriptor): Bool
    {
        return $descriptor->fileProcessable();
    }
        
}
