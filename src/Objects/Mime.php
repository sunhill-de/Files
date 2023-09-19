<?php

/**
 * @file fileobject.php
 * Provides the fileobject as a common basic for the other file objects (files, dirs, links)
 * Lang en
 * Reviewstatus: 2020-09-11
 * Localization: unknown
 * Documentation: unknown
 * Tests: unknown
 * Coverage: unknown
 * Dependencies: oo_object
 */
namespace Sunhill\Files\Objects;

use Sunhill\ORM\Objects\ORMObject;
use Sunhill\Files\Facades\FileManager;
use Sunhill\ORM\Objects\PropertyList;

/**
 * Abstract base class for all other fileobjects (files, dirs and links)
 *
 * @author lokal
 *        
 */
class Mime extends ORMObject {
    
    
    protected static function setupProperties(PropertyList $list)
    {
        $list->varchar('mimegroup')
            ->set_description('The group of the MIME type')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false);
        $list->varchar('item')
            ->setDefault(1)
            ->set_description('The item of the MIME type')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false);            
        $list->calculated('mime')
            ->searchable()
            ->set_decription('Complete mime string')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false)->searchable();            
        $list->varchar('default_ext')
            ->searchable()
            ->set_decription('The default extension')
            ->set_displayable(true)
            ->set_editable(true)
            ->set_groupeditable(false);
        $list->object('alias_for')
            ->setAllowedClass('mime');
    }

    public function calculate_mime() {
        return $this->mimegroup."/".$this->item;
    }
  
    protected static function setupInfos()
    {
        static::addInfo('name','Mime');
        static::addInfo('table','mimes');
        static::addInfo('name_s','mime',true);
        static::addInfo('name_p','mimes',true);
        static::addInfo('description','A class for mime types', true);
        static::addInfo('options',0);
        static::addInfo('editable',true);
        static::addInfo('instantiable',true);
    }
    
}
