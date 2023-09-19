<?php

/**
 * @file link.php
 * Provides the dir object 
 * Lang en
 * Reviewstatus: 2020-09-11
 * Localization: unknown
 * Documentation: unknown
 * Tests: unknown
 * Coverage: unknown
 * Dependencies: fileobject
 */
namespace Sunhill\Files\Objects;

use Sunhill\ORM\Objects\PropertyList;

/**
 * The class for links
 *
 * @author lokal
 *        
 */
class Link extends FileObject
{
    public function calculate_full_path() {
        return $this->parent_dir->full_path.$this->name.'.'.$this->ext;
    }
    
    protected static function setupProperties(PropertyList $list)
    {
        $list->object('target')
            ->setAllowedClass('File')
            ->setDefault(null)
            ->searchable()
            ->set_description('What file does this link point to')
            ->set_displayable(true)
            ->set_editable(true)
            ->set_groupeditable(false);
        $list->varchar('ext')
            ->set_description('The extension for this link')
            ->set_displayable(true)
            ->set_editable(true)
            ->set_groupeditable(true);
    }
 
    protected static function setupInfos()
    {
        static::addInfo('name','Link');
        static::addInfo('table','links');
        static::addInfo('name_s','link',true);
        static::addInfo('name_p','links',true);
        static::addInfo('description','A class for links', true);
        static::addInfo('options',0);
        static::addInfo('editable',false);
        static::addInfo('instantiable',false);
    }
    
    
}
