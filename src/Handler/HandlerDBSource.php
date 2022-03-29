<?php

namespace Sunhill\Files\Handler;

use Illuminate\Support\Facades\DB;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Facades\FileManager;

/**
 * Handles the entries in the database
 * @author klaus
 *
 */
class HandlerDBSource extends HandlerBase
{
    
    public static $prio = 52;

    function process(CrawlerDescriptor $descriptor)
    {
        $this->handleSource($descriptor);
    }

    protected function handleSource(CrawlerDescriptor $descriptor)
    {
        if ($descriptor->source[0] == ".") {
            $file = FileManager::normalizeFile(getcwd()."/".$descriptor->source);
        } else {
            $file = FileManager::normalizeFile($descriptor->source);
        }
        
        if (!($result = DB::table("sources")->where("file_id",$descriptor->file->ID)->where("source",$file)->first()))
        {
            DB::table("sources")->insert(["file_id"=>$descriptor->file->ID,"source"=>$file,"host"=>gethostname()]);
        }
    }
    
    function matches(CrawlerDescriptor $descriptor): Bool
    {
        return $descriptor->fileReadable() && !$descriptor->ignore_source &&
               (!$descriptor->fileInDatabase || !$descriptor->skip_duplicates)
                && !$descriptor->alreadyInMedia();
    }
    
    
}
