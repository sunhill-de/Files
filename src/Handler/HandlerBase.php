<?php

namespace Sunhill\Files\Handler;

use Illuminate\Support\Str;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Facades\FileManager;

abstract class HandlerBase
{
    protected $parent;
    
    protected $descriptor;
    
    static public $prio = 50;
    
    public function __construct($parent = null)
    {
        $this->parent = $parent;
    }
    
    public function error($message)
    {
        if (!is_null($this->parent)) {
            $this->parent->error($message);
        }
    }
    
    public function info($message)
    {
        if (!is_null($this->parent)) {
            $this->parent->info($message);
        }
    }
    
    public function verboseinfo($message)
    {
        if (!is_null($this->parent)) {
            $this->parent->verboseinfo($message);
        }
    }
    
    public function debug($message)
    {
        if (!is_null($this->parent)) {
            $this->parent->debug($message);
        }
    }
    
    public function fatal($message)
    {
        if (!is_null($this->parent)) {
            $this->parent->fatal($message);
        }
    }
    
    /*
    protected function normalizeDir($path)
    {
        $this->info("Deprecated method normalizeDir");
        return FileManager::normalizeDir($path);
    }
    
    protected function normalizeFile($path)
    {
        $this->info("Deprecated method normalizeFile");
        return FileManager::normalizeFile($path);
    }
    */
    
    /**
     * Adds the given dir to the descriptor
     * @param CrawlerDescriptor $descriptor
     * @param string $pathname
     */
    protected function addDir(CrawlerDescriptor $descriptor,string $pathname)
    {
        $descriptor->addDirs[] = $pathname;    
    }
    
    protected function addLink(CrawlerDescriptor $descriptor, string $input, $filename=null)
    {
        if (is_null($filename)) {
            $filename = pathinfo($input,PATHINFO_BASENAME);
            $pathname = Str::finish(pathinfo($input,PATHINFO_DIRNAME),DIRECTORY_SEPARATOR);
        } else {
            $pathname = Str::finish($input,DIRECTORY_SEPARATOR);
        }
        $this->addDir($descriptor,$pathname);
        $descriptor->addLinks[] = $pathname.$filename;
    }
    
    abstract function process(CrawlerDescriptor $descriptor);
    
    abstract function matches(CrawlerDescriptor $descriptor): Bool;
    
}
