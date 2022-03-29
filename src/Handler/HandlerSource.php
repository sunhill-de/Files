<?php

namespace Sunhill\Files\Handler;

use Illuminate\Support\Facades\DB;
use Sunhill\Files\CrawlerDescriptor;

/**
 * Handles the entries in the database
 * @author klaus
 *
 */
class HandlerSource extends HandlerBase
{
    
    public static $prio = 40;

    function process(CrawlerDescriptor $descriptor)
    {
        $this->handleSourceLinks($descriptor);
    }

    protected function handleSourceLinks(CrawlerDescriptor $descriptor)
    {
        $this->addLink($descriptor,"/sources/all".$descriptor->source);
    }
    
    function matches(CrawlerDescriptor $descriptor): Bool
    {
        return $descriptor->fileReadable() && !$descriptor->ignore_source &&
        (!$descriptor->fileWasInDatabase() || !$descriptor->skip_duplicates)
        && !$descriptor->alreadyInMedia();;
    }
    
    
}
