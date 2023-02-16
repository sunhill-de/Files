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
class FileObject extends ORMObject {
    
    public static $table_name = 'fileobjects';
    public static $object_infos = [
        'name'=>'FileObject',       // A repetition of static:$object_name @todo see above
        'table'=>'fileobjects',     // A repitition of static:$table_name
        'name_s'=>'file object',     // A human readable name in singular
        'name_p'=>'file objects',    // A human readable name in plural
        'description'=>'Baseobject for fileobjects like files, dirs or links',
        'options'=>0,           // Reserved for later purposes
    ];
    
    protected static function setupProperties() {
        parent::setupProperties();
        self::integer('fileobject_exists')
            ->setDefault(1)
            ->set_description('Does this file object (still) exists?')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false);
        self::integer('fileobject_created')
            ->setDefault(1)
            ->set_description('Was this fileobject created?')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false);            
        self::calculated('full_path')
            ->searchable()
            ->set_decription('Complete path of the fileobject')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false);            
        self::varchar('name')
            ->searchable()
            ->set_decription('The name of the fileobject (file name or dir name)')
            ->set_displayable(true)
            ->set_editable(true)
            ->set_groupeditable(false);
        self::object('parent_dir')
            ->setAllowedObjects('Dir')
            ->searchable()
            ->set_decription('Parentdir')
            ->set_displayable(true)
            ->set_editable(true)
            ->set_groupeditable(false);
        self::arrayOfObjects('associations')
            ->searchable()
            ->set_decription('Association to this fileobject')
            ->set_displayable(true)
            ->set_editable(true)
            ->set_groupeditable(true);            
    }
  
    
    protected static function setupInfos()
    {
        static::addInfo('name','FileObject');
        static::addInfo('table','fileobjects');
        static::addInfo('name_s','fileobjects',true);
        static::addInfo('name_p','fileobject',true);
        static::addInfo('description','Baseobject for fileobjects like files, dirs or links');
        static::addInfo('options',0);
        static::addInfo('editable',false);
        static::addInfo('instantiable',false);
    }
    
}
