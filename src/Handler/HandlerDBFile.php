<?php

namespace Sunhill\Files\Handler;

use Illuminate\Support\Facades\DB;
use Sunhill\Files\CrawlerDescriptor;

/**
 * Handles the entries in the database
 * @author klaus
 *
 */
class HandlerDBFile extends HandlerBase
{
    
    public static $prio = 51; // Execute after MoveDestination
 
    function process(CrawlerDescriptor $descriptor)
    {
        $descriptor->file->commit();
        $descriptor->dbstate->id = $descriptor->file->getID();
        $descriptor->dbstate->isInDatabase = true;
        
        $this->verboseinfo("File added to Database. ID is '".$descriptor->dbstate->id."'");
    }

    function matches(CrawlerDescriptor $descriptor): Bool
    {
        return $descriptor->fileReadable();
    }
    
    
}
