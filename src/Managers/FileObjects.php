<?php

namespace Sunhill\Files\Managers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Sunhill\Files\Objects\Dir;
use Sunhill\Files\Objects\File;
use Sunhill\Files\Facades\FileManager;

class FileObjects 
{
 
    /**
     * Normalized pathes always end with a slash and never start with one 
     * (except when explicity said to do so)
     * @param string $path
     * @param bool $leading_slash
     * @return string
     */
    public function normalizePath(string $path,bool $leading_slash = false): string
    {
        $path = FileManager::normalizeDir($path);
        if ($leading_slash) {
            if ($path[0] !== DIRECTORY_SEPARATOR) {
                $path = DIRECTORY_SEPARATOR.$path;
            }
        } else {
            if ($path[0] == DIRECTORY_SEPARATOR) {
                $path = substr($path,1);
            }
        }
        return Str::finish($path,DIRECTORY_SEPARATOR);   
    }
    
    /**
     * Normalizes the uses paths so that they are all uniformed
     * Normalized mean:
     * - No leading slash
     * - A trailing slash
     * - media dir removed (always relative to media dir)
     * @param string $path
     * @return string
     */
    public function normalizeMediaPath(string $path): string
    {
        $path = $this->normalizePath($path,true);
        // If the path is prefixed by media dir remove it
        $media = $this->normalizePath(FileManager::getMediaDir(),true);
        if (strpos($path,$media) === 0) {
            $path = substr($path,strlen($media));
        }
        if ($path[0] == DIRECTORY_SEPARATOR) {
            $path = substr($path,1);
        }
        return $path;
    }
    
    /**
     * Looks up the given directory $path in the database and returns its id or 0 if not found
     */
    public function searchDirID(string $path): Int
    {
        if ($dir = $this->searchDir($path)) {
            return $dir->getID();
        } else {
            return 0;
        }
    }
    
    /**
     * Looks up the given directory in the database and returns the dir object or null if not found
     * @param string $path
     * @return \Illuminate\Database\Eloquent\Model|object|\Illuminate\Database\Query\Builder|NULL|NULL
     */
    public function searchDir(string $path)
    {
        $result = Dir::search()->where('full_path','=',$this->normalizeMediaPath($path))->loadIfExists();
        if ($result) {
            return $result;
        } else {
            return null;
        }
    }
    
    public function searchOrInsertDir(string $path)
    {
        $path = $this->normalizeMediaPath($path);
        if (empty($path))
        {
            return null;
        }
        if ($dir = $this->searchDir($path)) {
            return $dir;
        }
        $dir = new Dir();
        $path_parts = explode(DIRECTORY_SEPARATOR,$path);
        array_pop($path_parts); // ignore trailing slash
        $dir->name = array_pop($path_parts);
        $dir->parent_dir = $this->searchOrInsertDir(implode(DIRECTORY_SEPARATOR,$path_parts).DIRECTORY_SEPARATOR);
        $dir->commit();
        
        return $dir;
    }
    
    public function searchFileIDByHash(string $hash): Int
    {
        if ($result = DB::table("files")->where("sha1_hash",$hash)->first()) {
            return $result->id;
        } else {
            return 0;
        }
    }
    
    public function searchFileByHash(string $hash): File
    {
        if ($result = File::search()->where("sha1_hash","=",$hash)->loadIfExists()) {
            return $result;
        } else {
            return null;
        }
    }
    
}
