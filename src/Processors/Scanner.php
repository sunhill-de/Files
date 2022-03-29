<?php

namespace Sunhill\Files\Processors;

use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Facades\FileManager;
use Sunhill\Files\Facades\FileObjects;
use Sunhill\Files\Handler\HandlerAdditional;
use Sunhill\Files\Handler\HandlerDBFile;
use Sunhill\Files\Handler\HandlerDestination;
use Sunhill\Files\Handler\HandlerDirs;
use Sunhill\Files\Handler\HandlerFileObject;
use Sunhill\Files\Handler\HandlerFileStatus;
use Sunhill\Files\Handler\HandlerLinks;
use Sunhill\Files\Handler\HandlerMoveDestination;
use Sunhill\Files\Handler\HandlerSource;
use Sunhill\Files\Handler\HandlerDBSource;
use Sunhill\Files\Objects\Dir;
use Sunhill\Files\Objects\Link;

class Scanner extends CrawlerBase
{
 
    protected $skip_duplicates;
    
    protected $ignore_source;
    
    protected $erase_empty;
    
    protected $tags;
    
    protected $associations;
    
    protected $called_command = 'scan';
    
    public function __construct($command,bool $keep,bool $recursive = true, 
                         bool $skip = false, bool $ignore_source = false, bool $erase_empty, 
                         int $verbosity,$tags = null,$assocations = null)
    {
        parent::__construct($recursive,$verbosity);
        $this->command = $command;
        $this->keep = $keep;
        $this->skip_duplicates = $skip;
        $this->ignore_source = $ignore_source;
        $this->tags = $tags;
        $this->associations = $assocations;
        $this->erase_empty = $erase_empty;
    }
    
    /**
     * Does the crawling
     * @param unknown $command
     * @param unknown $target
     * @param unknown $keep
     * @param unknown $verbosity
     */
    public function scan(string $target) 
    {
        if (!file_exists($target)) {
            $this->error("The file/directory $target does not exist.");
            return;            
        }
        $this->handleEntry($target);
    }

    protected function enterDir($target)
    {
        parent::enterDir($target);
        
        if (FileManager::fileInDir($target,FileManager::getMediaDir())) {
            FileObjects::searchOrInsertDir($target);
        }
    }
    
    protected function leaveDir($target)
    {
        parent::leaveDir($target);
        if ($this->erase_empty) {
            FileManager::eraseDirIfEmpty($target);
        }    
    }
    
    protected function handleLink($target)
    {
        if (FileManager::fileInDir($target,FileManager::getMediaDir())) {
            $link_target = readlink($target);
            $this->handleFile($link_target); // In case, the target is not yet added
            $target_dir = FileObjects::normalizeMediaPath(pathinfo($target,PATHINFO_DIRNAME));
            $parent_dir = FileObjects::searchOrInsertDir($target_dir);
            
            $link = new Link();
            $link->name = pathinfo($target,PATHINFO_FILENAME);
            $link->ext = pathinfo($target,PATHINFO_EXTENSION);
            $link->parent_dir = $parent_dir;
            $link->target = FileObjects::searchFileByHash(pathinfo($link_target,PATHINFO_FILENAME));
            $link->commit();
        }
    }
    
    protected function getHandlers()
    {
        return [
            HandlerAdditional::class,
            HandlerDBFile::class,
            HandlerDBSource::class,
            HandlerDestination::class,
            HandlerDirs::class,
            HandlerFileObject::class,
            HandlerFileStatus::class,
            HandlerLinks::class,
            HandlerMoveDestination::class,
            HandlerSource::class,
        ];
    }
}
