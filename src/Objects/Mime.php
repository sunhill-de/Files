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

/**
 * Abstract base class for all other fileobjects (files, dirs and links)
 *
 * @author lokal
 *        
 */
class Mime extends ORMObject {
    
    public static $table_name = 'mimes';
    public static $object_infos = [
        'name'=>'Mime',       // A repetition of static:$object_name @todo see above
        'table'=>'mimes',     // A repitition of static:$table_name
        'name_s'=>'MIME Type',     // A human readable name in singular
        'name_p'=>'MIME Types',    // A human readable name in plural
        'description'=>'Storage for MIME types',
        'options'=>0,           // Reserved for later purposes
    ];
    
    protected static function setupProperties() {
        parent::setupProperties();
        self::varchar('mimegroup')
            ->set_description('The group of the MIME type')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false);
        self::varchar('item')
            ->setDefault(1)
            ->set_description('Was this fileobject created?')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false);            
        self::calculated('mime')
            ->searchable()
            ->set_decription('Complete mime string')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false)->searchable();            
        self::varchar('default_ext')
            ->searchable()
            ->set_decription('The default extension')
            ->set_displayable(true)
            ->set_editable(true)
            ->set_groupeditable(false);
        self::object('alias_for')
            ->setAllowedObjects(['mime']);
    }

    public function calculate_mime() {
        return $this->mimegroup."/".$this->item;
    }
        
}
