<?php

namespace Sunhill\Files\Handler;

use Illuminate\Support\Facades\DB;
use Sunhill\Files\CrawlerDescriptor;

/**
 * Removes a file that is already in the media storage and all links to it
 * @author klaus
 *
 */
class HandlerHash extends HandlerBase
{
 
    public static $prio = 5;
    
    function process(CrawlerDescriptor $descriptor)
    {
    }

    private function removeFile(CrawlerDescriptor $descriptor)
    {
    }
  
    private function removeLinks(CrawlerDescriptor $descriptor)
    {
    }
  
    function matches(CrawlerDescriptor $descriptor): Bool
    {
        return $descript->fileInStorage() && $descriptor->stateIs(['ignored','converted','deleted']);
    }
    
}
