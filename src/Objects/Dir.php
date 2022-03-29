<?php

/**
 * @file dir.php
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

use Sunhill\Files\Facades\FileManager;

/**
 * The class for dirs
 *
 * @author lokal
 *        
 */
class Dir extends FileObject
{
    public static $table_name = 'dirs';
    
    public static $object_infos = [
        'name'=>'Dir',       // A repetition of static:$object_name @todo see above
        'table'=>'dirs',     // A repitition of static:$table_name
        'name_s' => 'directory',
        'name_p' => 'directories',
        'description' => 'Class for directories',
        'options'=>0,           // Reserved for later purposes
    ];
    
    protected static function setupProperties()
    {
        parent::setupProperties();
        self::integer('max_files')
            ->setDefault(0)
            ->set_description('How many files per directory are allowed (0=no limit)')
            ->set_displayable(true)
            ->set_editable(true)
            ->set_groupeditable(false);
        self::integer('max_levels')
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
    
}
