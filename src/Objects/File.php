<?php

/**
 * @file file.php
 * Provides the file object 
 * Lang en
 * Reviewstatus: 2020-09-11
 * Localization: unknown
 * Documentation: unknown
 * Tests: unknown
 * Coverage: unknown
 * Dependencies: fileobject
 */
namespace Sunhill\Files\Objects;

use Sunhill\Files\Facades\FileManager;
use Sunhill\ORM\Objects\PropertyList;
use Sunhill\ORM\Properties\PropertyVarchar;

/**
 * The class for files. This class provides informations about a spcecific file. The file itself is not able
 * to move or erase itself. This has to be done by a higher instance. Also the fill can't set links to itself. 
 * All changes to the file have to be coordinated by a higher instance (like the scanner). 
 *
 * @author lokal
 *        
 */
class File extends FileObject {

    protected $current_location = '';
    
    protected static function setupProperties(PropertyList $list)
    {
        $list->object('reference')
            ->setAllowedObjects('File')
            ->setDefault(null)
            ->set_description('Referenced file')
            ->set_displayable(true)
            ->set_editable(true)
            ->set_groupeditable(false);
        $list->varchar('sha1_hash')
            ->setMaxLen(40)
            ->searchable()
            ->set_description('SHA1-Hash of the whole file.')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false);
        $list->varchar('md5_hash')
            ->setMaxLen(32)
            ->searchable()
            ->set_description('The md5 hash of the whole file')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false);
        $list->varchar('ext')
            ->set_description('The extension of this file')
            ->set_displayable(true)
            ->set_editable(true)
            ->set_groupeditable(true);
        $list->object('mime')
            ->setAllowedObjects('Mime')
            ->set_description('The mime type of this file')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false);
        $list->varchar('checkout_state')
            ->setDefault('')
            ->searchable()
            ->set_description('Whats the checkout state of this file')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false);            
        $list->enum('type')
            ->setEnumValues([
                'regular',              // Normal file
                'converted_to',         // This file war permanently converted to another file (this file isn't existing anymore but is not deleted)
                'deleted',              // This file was deleted (not converted)
                'ignored',              // This file is ignored
                'converted_from',       // This file was converted from another file (linked in reference). The other file has type converted_to
                'alterated_from'])      // This file was alterated from another file (linked in reference). The other file keeps the state regular
            ->set_description('Type of this file')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false);
        $list->datetime('created')
            ->set_description('Timestamp of the creation of this file.')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false);
        $list->datetime('changed')
            ->set_description('Timestamp of the last change of this file.')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false);
        $list->integer('size')
            ->set_description('Size of the file (in bytes)')
            ->set_displayable(true)
            ->set_editable(false)
            ->set_groupeditable(false);
        $list->array('sources')
            ->setElementType(PropertyVarchar::class)
            ->set_description('The source dir(s) this file was read from.')
            ->set_displayable(true)
            ->set_editable(true)
            ->set_groupeditable(false);
        $list->array('content')
            ->setElementType(PropertyObject::class)
            ->setAllowedObjects(['Person','Location','Date'])
            ->setDefault('none')
            ->set_description('Linked contents')
            ->set_displayable(true)
            ->set_editable(true)
            ->set_groupeditable(false);
    }
    
    function calculate_full_path()
    {
        if (is_null($this->parent_dir)) {
            return $this->sha1_hash.'.'.$this->ext;       
        } else {
            return $this->parent_dir->full_path.'.'.$this->sha1_hash.'.'.$this->ext;
            
        }
    }
    
    public function getDefaultDate()
    {
        return $this->created;
    }
    
    public function getDefaultName()
    {
        return $this->name.".".$this->ext;
    }
    
    protected static function setupInfos()
    {
        static::addInfo('name','File');
        static::addInfo('table','files');
        static::addInfo('name_s','file',true);
        static::addInfo('name_p','files',true);
        static::addInfo('description','Class for files', true);
        static::addInfo('options',0);
        static::addInfo('editable',true);
        static::addInfo('instantiable',false);
    }
    
}
