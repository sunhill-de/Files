<?php

/**
 * @file dir.php
 * Provides the dir object 
 * Lang en
 * Reviewstatus: 2023-09-06
 * Localization: unknown
 * Documentation: unknown
 * Tests: unknown
 * Coverage: unknown
 * Dependencies: fileobject
 */
namespace Sunhill\Files\Objects;

use Sunhill\Files\Facades\FileManager;
use Sunhill\ORM\Objects\PropertyList;

/**
 * The class for dirs
 *
 * @author lokal
 *        
 */
class Dir extends FileObject
{
    
    protected static function setupProperties(PropertyList $list)
    {
        $list->integer('max_files')
            ->setDefault(0)
            ->set_description('How many files per directory are allowed (0=no limit)')
            ->set_displayable(true)
            ->set_editable(true)
            ->set_groupeditable(false);
        $list->integer('max_levels')
            ->setDefault(0)
            ->set_description('How deep can we built a directory tree under this directory (0=no limit)')
            ->set_displayable(true)
            ->set_editable(true)
            ->set_groupeditable(true);
    }
    
    public function calculate_full_path() {
        $parent = $this->parent_dir;
        if (!is_null($parent)) {
            return $this->parent_dir->full_path.$this->name.'/';
        } else {
            return $this->name.'/';
        }
    }

    protected static function setupInfos()
    {
        static::addInfo('name','Dir');
        static::addInfo('table','dirs');
        static::addInfo('name_s','dir',true);
        static::addInfo('name_p','dirs',true);
        static::addInfo('description','A class for directories', true);
        static::addInfo('options',0);
        static::addInfo('editable',false);
        static::addInfo('instantiable',false);
    }
    
}
