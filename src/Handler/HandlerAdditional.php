<?php

namespace Sunhill\Files\Handler;

use Illuminate\Support\Facades\DB;
use Sunhill\Files\CrawlerDescriptor;

/**
 * Handles the entries in the database
 * @author klaus
 *
 */
class HandlerAdditional extends HandlerBase
{
    
    public static $prio = 40;

    function process(CrawlerDescriptor $descriptor)
    {
        $this->handleMimeLink($descriptor);
        $this->handleSizeLink($descriptor);
    }

    protected function handleMimeLink(CrawlerDescriptor $descriptor)
    {
        $this->addLink($descriptor,"/additional/mime/by_hash/".$descriptor->filestate->mime_str.'/'.$this->getHashFileName($descriptor));
        $this->addLink($descriptor,"/additional/mime/by_name/".$descriptor->filestate->mime_str.'/'.$descriptor->file->getDefaultName());
    }

    protected function handleSizeLink(CrawlerDescriptor $descriptor)
    {
        if ($descriptor->file->size < 1000) {
            $this->addLink($descriptor,"/additional/size/tiny/by_hash/".$this->getHashFileName($descriptor));
            $this->addLink($descriptor,"/additional/size/tiny/by_name/".$descriptor->file->getDefaultName());
        } else if ($descriptor->file->size < 1000000) {
            $this->addLink($descriptor,"/additional/size/small/by_hash/".$this->getHashFileName($descriptor));            
            $this->addLink($descriptor,"/additional/size/small/by_name/".$descriptor->file->getDefaultName());
        } else if ($descriptor->file->size < 100000000) {
            $this->addLink($descriptor,"/additional/size/normal/by_hash/".$this->getHashFileName($descriptor));            
            $this->addLink($descriptor,"/additional/size/normal/by_name/".$descriptor->file->getDefaultName());
        } else {
            $this->addLink($descriptor,"/additional/size/huge/by_hash/".$this->getHashFileName($descriptor));            
            $this->addLink($descriptor,"/additional/size/huge/by_name/".$descriptor->file->getDefaultName());
        }        
    }
    
    private function getHashFileName(CrawlerDescriptor $descriptor)
    {
        return $descriptor->filestate->sha1_hash.'.'.$descriptor->file->ext;
    }
    
    function matches(CrawlerDescriptor $descriptor): Bool
    {
        return $descriptor->fileReadable();
    }
    
    
}
