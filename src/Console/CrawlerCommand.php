<?php

namespace Sunhill\Files\Console;

trait CrawlerCommand 
{

    protected function getSwitch(string $name_pos,string $name_neg,bool $default)
    {
        $pos = $this->option($name_pos);
        $neg = $this->option($name_neg);
        if ($pos && $neg) {
            $this->error("'$name_pos' and '$name_neg' can't be switch on the same time");
            die();
        }
        if (!$pos && !$neg) {
            return $default;
        } else if ($pos) {
            return true;
        } else {
            return false;
        }
    }
    
    protected function getSync()
    {
        return $this->getSwitch('sync','no-sync',false);    
    }
    
    protected function getRecursive()
    {
        return $this->getSwitch('recursive','no-recursive',true);
    }
    
    protected function getSkipDuplicates()
    {
        return $this->getSwitch('skip-duplicates','no-skip-duplicates',false);
    }
    
    protected function getSuppressSource()
    {
        return $this->getSwitch('suppress-source','no-suppress-source',false);
    }
    
    protected function getTags()
    {
        if (!empty($tags = $this->Option('tags'))) {
            $this->error("Tags are not supportet yet. Ignoring.");
        }
    }
    
    protected function getAssociations()
    {
        if (!empty($associations = $this->Option('associations'))) {
            $this->error("Associations are not supportet yet. Ignoring.");           
        }
    }
    
}