<?php
/**
 * @file FileManager.php
 * Provides the FileManager object for handling of fileobjects
 * Lang en
 * Reviewstatus: 2021-09-22
 * Localization: unknown
 * Documentation: unknown
 * Tests: tests/Unit/Manager/FileManagerTest.php
 * Coverage: unknown
 */
namespace Sunhill\Files\Managers;

use Illuminate\Support\Str;
use Sunhill\Files\Objects\file;
use Sunhill\Files\Objects\dir;
use Sunhill\Files\Objects\link;
use Sunhill\Files\Facades\Detector;


/**
 * This manager deals with the media directory filesystem and the filesystem outside media. It can move and delete files
 * Move, create and delete directories and create and delete links. It also provides some methods to simplify directories 
 * and get relative links.  
 * @author klaus
 *
 */
class FileManager {
 
    private $media_dir;
    
    private $mime_associations = [];
    
    /**
     * Returns the current root for the media directory
     */
    public function getMediaDir() 
    {
        return config('crawler.media_dir');
    }
 
    /**
     * Returns the absolute path of the given path
     * If $path starts with an / than this path is already absolute and returned
     * If not the path is prepended with the media dir root and returned
     */
    public function getAbsolutePath(string $path,bool $file=false): string
    {
       if ($path[0] == DIRECTORY_SEPARATOR) {
           return $path;
       } else {
           return Str::finish($this->getMediaDir(),DIRECTORY_SEPARATOR).$path;
       } 
    }
  
    /**
     * Tests, if an directory entry exists (could be a file, directory or link)
     * Is just and capsulation of file_exists
     *
     * @param string $test
     *            Entry to test
     * @return bool True, if it does exist otherwise false
     */
    public function entryExists(string $test): bool
    {
        return file_exists($this->getAbsolutePath($test));
    }
     
    /**
     * Tests if an directory exists and is really a directory
     *
     * @param string $test
     *            The Directory to test
     * @return boolean True, if it does exists and is a directory otherwise false
     */
    public function dirExists(string $test): bool
    {
       $test = $this->getAbsolutePath($test); 
       return file_exists($test) && is_dir($test);
    }
  
    /**
     * Tests if a directory is existing and readable
     *
     * @param string $test
     *            The directory to test
     * @return boolean True, if it does exists and is readable otherwise false
     */
    public function dirReadable(string $test): bool
    {
       $test = $this->getAbsolutePath($test);
       return $this->dirExists($test) && (is_readable($test));
    }
     
    /**
     * Tests if a directory is existing and writable
     *
     * @param string $test
     *            The directory to test
     * @return boolean True, if it does exists and is writable otherwise false
     */
    public function dirWritable(string $test): bool
    {
       $test = $this->getAbsolutePath($test);
       return $this->dirExists($test) && (is_writable($test));
    }
    
    /**
     * Returns the subdirectories of the given directory
     *
     * @param string $dir
     *            The directory to test
     * @return unknown[] array of string
     */
    public function getSubdirectories(string $dir): array
    {
        $dir = $this->getAbsolutePath($dir);
        if (! $this->dirReadable($dir)) {
            throw new FileManagerException(__("The given dir ':dir' does not exist or is not readable.",['dir'=>$dir]));
        }
        $result = [];
        $directory = dir($dir);
        while (false !== ($entry = $directory->read())) {
            if (($entry !== '.') && ($entry !== '..')) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $entry)) {
                    $result[] = $entry;
                }
            }
        }
        return $result;
    }
  
    /**
     * Returns the files of the given directory
     *
     * @param string $dir
     *            The directory to test
     * @return unknown[] array of string
     */
    public function getFiles(string $dir): array
    {
        $dir = $this->getAbsolutePath($dir);
        if (! $this->dirReadable($dir)) {
            throw new FileManagerException(__("The given dir ':dir' does not exist or is not readable.",['dir'=>$dir]));
        }
        $result = [];
        $directory = dir($dir);
        while (false !== ($entry = $directory->read())) {
            if (($entry !== '.') && ($entry !== '..')) {
                if (is_file($dir . DIRECTORY_SEPARATOR . $entry) && ! is_link($dir . DIRECTORY_SEPARATOR . $entry)) {
                    $result[] = $entry;
                }
            }
        }
        return $result;
    }
    
    /**
     * Returns the links of the given directory
     *
     * @param string $dir
     *            The directory to test
     * @return unknown[] array of string
     */
    public function getLinks(string $dir): array
    {
        $dir = $this->getAbsolutePath($dir);
        
        if (! $this->dirReadable($dir)) {
            throw new FileManagerException(__("The given dir ':dir' does not exist or is not readable.",['dir'=>$dir]));
        }
        $result = [];
        $directory = dir($dir);
        while (false !== ($entry = $directory->read())) {
            if (($entry !== '.') && ($entry !== '..')) {
                if (is_link($dir . DIRECTORY_SEPARATOR . $entry)) {
                    $result[] = $entry;
                }
            }
        }
        return $result;
    }
    
    /**
     * Returns all entries of the given directory
     *
     * @param string $dir
     *            The directory to test
     * @param bool $group
     *            If true, then all entries are grouped in dirs, files or links otherwise all entries are returned
     *            with their respective class (dir, file or link)
     * @return unknown[] array of string
     */
    public function getEntries(string $dir, bool $group = false): array
    {
        $dir = $this->getAbsolutePath($dir);
        
        if (! $this->dirReadable($dir)) {
            throw new FileManagerException(__("The given dir ':dir' does not exist or is not readable.",['dir'=>$dir]));
        }
        if ($group) {
            $result = [
                'dirs' => [],
                'files' => [],
                'links' => []
            ];
        } else {
            $result = [];
        }
        $directory = dir($dir);
        while (false !== ($entry = $directory->read())) {
            if (($entry !== '.') && ($entry !== '..')) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $entry)) {
                    if ($group) {
                        $result['dirs'][] = $entry;
                    } else {
                        $result[$entry] = 'dir';
                    }
                } else if (is_link($dir . DIRECTORY_SEPARATOR . $entry)) {
                    if ($group) {
                        $result['links'][] = $entry;
                    } else {
                        $result[$entry] = 'link';
                    }
                } else if (is_file($dir . DIRECTORY_SEPARATOR . $entry)) {
                    if ($group) {
                        $result['files'][] = $entry;
                    } else {
                        $result[$entry] = 'file';
                    }
                }
            }
        }
        return $result;
    }
    
    /**
     * Tests if $file is in $dir or one of its subdirs
     *
     * @param string $file
     * @param string $dir
     * @return boolean
     */
    public function fileInDir(string $file, string $dir): bool
    {
        $file = $this->getAbsolutePath($file);
        $dir = $this->getAbsolutePath($dir);
        
        $file = $this->normalizeFile($file);
        $dir = $this->normalizeDir($dir);
        $path = Str::finish(pathinfo($file, PATHINFO_DIRNAME), DIRECTORY_SEPARATOR);
        return (strpos($path, Str::finish($dir, DIRECTORY_SEPARATOR)) === 0);
    }
    
    /**
     * Tests if $file is in $dir or one of its subdirs
     *
     * @param string $file
     * @param string $dir
     * @return boolean
     */
    public function dirInDir(string $file, string $dir): bool
    {
        $file = $this->getAbsolutePath($file);
        $dir = $this->getAbsolutePath($dir);
        
        return (strpos(Str::finish($file, DIRECTORY_SEPARATOR), Str::finish($dir, DIRECTORY_SEPARATOR)) === 0);
    }
    
    /**
     * Renames the given dir to the new name
     *
     * @param string $source
     * @param string $dest
     */
    /*
     * /a/b -> /a/b
     * /a/b -> /a/bb
     * /a/b -> /aa/b
     * /a/b -> /c/d
     * /a/b -> /c
     * /a/b/c -> a/d
     * /a/b/c -> a/d/e
     */
    public function renameDir(string $source, string $dest)
    {
        $source = $this->getAbsolutePath($source);
        $dest = $this->getAbsolutePath($dest);
        
        if ($source === $dest) {
            return true; // nothing to do
        }
        if (! $this->dirExists($source)) {
            throw new FileManagerException(__("The source ':source' does not exists.",['source'=>$source]));
        }
        if ($this->dirExists($dest)) {
            throw new FileManagerException(__("The destination ':dest' exists.",['dest'=>$dest]));
        }
        $source_parts = explode(DIRECTORY_SEPARATOR, $source);
        $dest_parts = explode(DIRECTORY_SEPARATOR, $dest);
        /**
         *
         * @todo We don't need both strings because they are equal in the end
         */
        $source_build = '';
        $dest_build = '';
        for ($i = 0; $i < count($source_parts); $i ++) {
            if ($i < count($dest_parts)) {
                $dest_build .= DIRECTORY_SEPARATOR . $dest_parts[$i];
                if ($source_parts[$i] !== $dest_parts[$i]) {
                    rename(Str::finish($source_build . DIRECTORY_SEPARATOR . $source_parts[$i], DIRECTORY_SEPARATOR), Str::finish($dest_build, DIRECTORY_SEPARATOR));
                }
                $source_build .= DIRECTORY_SEPARATOR . $dest_parts[$i];
            }
        }
    }
    
    /**
     * Erases the passed dir
     * @param string $path
     * @throws FileManagerException
     */
    public function eraseDir(string $path,bool $recursive=false)
    {
        $path = $this->getAbsolutePath($path);
        
        $entries = $this->getEntries($path);
        if (!empty($entries)) {
            if (!$recursive) {
                throw new FileManagerException(__("The directory ':path' is not empty.",['path'=>$path]));
            }
            $this->eraseDirRecursive($path,$entries);
        }        
        if (! rmdir($path) || file_exists($path)) {
                throw new FileManagerException(__("The directory ':path' could not be erased.",['path'=>$path]));
        }
    }
    
    private function eraseDirRecursive(string $root,array $entries) {
        foreach ($entries as $entry => $type) {
            switch ($type) {
                case 'file':
                    $this->deleteFile($root.DIRECTORY_SEPARATOR.$entry);
                    break;
                case 'link':
                    $this->removeLink($root.DIRECTORY_SEPARATOR.$entry);
                    break;
                case 'dir':
                    $this->eraseDir($root.DIRECTORY_SEPARATOR.$entry);
                    break;
            }
        }
    }
    
    /**
     * Creates a new directory
     * @param string $path Directory to create
     * @param bool $ignore_existing if true and the directory already exsists dont raise an exception
     * @throws FileManagerException
     */
    public function createDir(string $path,bool $ignore_existing=false) 
    {
        $path = $this->getAbsolutePath($path);
        
        if (file_exists($path)) {
            if ($ignore_existing) {
                return;
            }
            throw new FileManagerException(__("The directory ':path' already exists.",['path'=>$path]));            
        }
        mkdir($path);
    }
    
    /**
     * Creates a new directory
     * @param string $path Directory to create
     * @param bool $ignore_existing if true and the directory already exsists dont raise an exception
     * @throws FileManagerException
     */
    public function createDirRecursive(string $path,bool $ignore_existing=false) 
    {
        $path = $this->getAbsolutePath($path);
        
        if (file_exists($path)) {
            if ($ignore_existing) {
                return;
            }
            throw new FileManagerException(__("The directory ':path' already exists.",['path'=>$path]));
        }
        mkdir($path,0777,true);
    }
    
    /**
     * Returns the effective directory that means a directory with no double slashes and no . or ..
     *
     * @param string $path: The directory to clean
     * @return string The cleanes directory
     */
    public function get_effective_dir($path, bool $trailing_slash = true)
    {
        $leading_slash = (substr($path, 0, 1) == DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '';
        $path = str_replace(array(
            DIRECTORY_SEPARATOR,
            '\\'
        ), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part)
                continue;
                if ('..' == $part) {
                    array_pop($absolutes);
                } else {
                    $absolutes[] = $part;
                }
        }
        $return = $leading_slash . implode(DIRECTORY_SEPARATOR, $absolutes);
        if ($trailing_slash) {
            return (substr($return, - 1) == DIRECTORY_SEPARATOR) ? $return : $return . DIRECTORY_SEPARATOR;
        } else {
            return $return;
        }
    }
    
    /**
     * Alias for get_effective_dir
     * @param unknown $path
     * @param bool $trailing_slash
     * @return string
     */
    public function normalizeDir($path,bool $trailing_slash = true) {
        return $this->get_effective_dir($path,$trailing_slash);    
    }

    /**
     * Returns the relative dir between $linkdir and $target_dir
     * @param unknown $link_dir
     * @param unknown $target_dir
     * @return string
     */
    public function getRelativeDir(string $link_dir, string $target_dir): string
    {
        $source = explode(DIRECTORY_SEPARATOR, Str::finish($link_dir,DIRECTORY_SEPARATOR));
        array_pop($source); // Trailing /
        $dest = explode(DIRECTORY_SEPARATOR, Str::finish($target_dir,DIRECTORY_SEPARATOR));
        array_pop($dest);
        $i = 0;
        while (($i < count($source) && ($i < count($dest)) && ($source[$i] == $dest[$i]))) {
            $i ++;
        }
        $result = str_repeat('..'.DIRECTORY_SEPARATOR, count($source) - $i);
        while ($i < count($dest)) {
            $result .= $dest[$i] . DIRECTORY_SEPARATOR;
            $i ++;
        }
        return $result;
    }

    /**
     * Checks if both passed dirs are the same and respect trailing slashes
     * @param string $dir1
     * @param string $dir2
     * @return boolean
     */
    public function dirsEqual(string $dir1,string $dir2): bool 
    {
        $dir1 = $this->normalizeDir($dir1);
        $dir2 = $this->normalizeDir($dir2);
        
        if (substr($dir1,-1) == DIRECTORY_SEPARATOR) {
            $dir1 = substr($dir1,0,-1);
        }
        if (substr($dir2,-1) == DIRECTORY_SEPARATOR) {
            $dir1 = substr($dir2,0,-1);
        }
        return $dir1 === $dir2;
    }

    /**
     * Checks if the given dir is empty or not
     * @param $path The dir to test
     * @return bool True if it is empty or false
     */
    public function dirEmpty(string $path): bool
    {
        $path = $this->normalizeDir($path);
        if (!is_dir($path)) {
            throw FileManagerException("'$path' is not a dir.");
        }
        $dir = dir($path);
        while (false !== ($entry = $dir->read())) {
            if (($entry == '.') || ($entry == '..')) {
                continue;
            }
            $dir->close();
            return false;            
        }
        $dir->close();
        return true;
    }
    
    /**
     * Checks if the given dir is empty: If yes, it deletes it
     * @param $path The dir to test
     * @return bool True if it is empty and eraseDir did work or false if not empty or eraseDir didn't work
     */
    public function eraseDirIfEmpty(string $path): bool
    {
        if (!$this->dirEmpty($path)) {
            return false;
        } else {
            $this->eraseDir($path);
            return true;
        }    
    }
    
    /**
     * Tests if $test is existing and a link
     *
     * @param string $test
     * @return boolean
     */
    public function linkExists(string $test): bool
    {
        $test = $this->getAbsolutePath($test);
        
        return file_exists($test) && is_link($test);
    }
    
    /**
     * Tests if $test is existing, a link and linking to an existing file
     *
     * @param string $test
     * @return boolean
     */
    public function linkTargetExists(string $test): bool
    {
        $test = $this->getAbsolutePath($test);
        
        return $this->linkExists($test) && file_exists(readlink($test));
    }

    /**
     * Tests if $test is an absolute or relative link
     *
     * @param string $test
     * @throws FileManagerException
     * @return boolean
     */
    public function linkIsRelative(string $test): bool
    {
        $test = $this->getAbsolutePath($test);
        
        if (! $this->linkExists($test)) {
            throw new FileManagerException(__("The given link ':test' does not exist.",['test'=>$test]));
        }
        return (strpos(readlink($test), '..'.DIRECTORY_SEPARATOR) !== false);
    }
    
    /**
     * Removes the link $link
     *
     * @param string $link
     * @throws FileManagerException
     */
    public function removeLink(string $link, bool $force = false): bool
    {
        $link = $this->getAbsolutePath($link);
        
        if (! $force) {
            if (! $this->linkExists($link)) {
                throw new FileManagerException(__("The given link ':link' does not exist.",['link'=>$link]));
            }
        }
        if (! unlink($link) || (file_exists($link))) {
            throw new FileManagerException(__("Deletion of ':link' failed.",['link'=>$link]));
        }
        return true;
    }
        
    /**
     * Creates a link 'link' to target
     *
     * @param string $link
     * @param string $target
     * @throws FileManagerException
     */
    public function createLink(string $link,string $target): void
    {
        $link = $this->getAbsolutePath($link,true);
        if (substr($target,0,1) !== '.') {
            $target = $this->getAbsolutePath($target,true);
        }
        
        if (strpos($target, '..'.DIRECTORY_SEPARATOR) !== false) {
            $target_path = pathinfo($link, PATHINFO_DIRNAME);
            if (! $this->entryExists($target_path . DIRECTORY_SEPARATOR . $target)) {
                throw new FileManagerException(__("The target ':target' does not exist.\n Command: create_link(:link,:target)",['link'=>$link,'target'=>$target]));
            }
        } else if (! $this->entryExists($target)) {
            throw new FileManagerException(__("The target ':target' does not exist.",['target'=>$target]));
        }
        exec("ln -s '".$target."' '".$link."'");
        if (! file_exists($link)) {
            throw new FileManagerException(__("Linking of ':link' to ':target' failed.",['link'=>$link,'target'=>$target]));
        }        
    }
    
    /**
     * Tests if $test is existing and a file
     *
     * @param string $test
     * @return boolean
     */
    public function fileExists(string $test): bool
    {
        $test = $this->getAbsolutePath($test);
        
        return (file_exists($test) && is_file($test) && ! is_link($test));    
    }
    
    /**
     * Tests if $test is existing,a file and readable
     *
     * @param string $test
     * @return boolean
     */
    public function fileReadable(string $test): bool
    {
        $test = $this->getAbsolutePath($test);
        
        return $this->fileExists($test) && (is_readable($test));
    }

    /**
     * Tests if $test is existing,a file and writable
     *
     * @param string $test
     * @return boolean
     */
    public function fileWritable(string $test): bool
    {
        $test = $this->getAbsolutePath($test);
        
        return $this->fileExists($test) && (is_writable($test));
    }

    /**
     * Alias for $this->normalize_file()
     * @param string $file
     */
    public function NormalizeFile(string $file) 
    {
        return $this->normalizeDir(Str::finish(pathinfo($file, PATHINFO_DIRNAME), DIRECTORY_SEPARATOR)).pathinfo($file, PATHINFO_BASENAME);
    }
    
    /**
     * Deletes the given file
     * @param string $file
     * @throws FileManagerException
     */
    public function deleteFile(string $file): void
    {
        if (! $this->fileWritable($file)) {
            throw new FileManagerException(__("The target ':file' does not exist or is not deletable.",['file'=>$file]));
        }
        if (! unlink($file) || file_exists($file)) {
            throw new FileManagerException(__("Deleting of ':file' failed.",['file'=>$file]));
        }
    }
        
    /**
     * Copies the file from source to dest
     * @param string $source
     * @param string $dest
     * @throws FileManagerException
     */
    public function copyFile(string $source, string $dest): void
    {
        $source = $this->getAbsolutePath($source);
        $dest = $this->getAbsolutePath($dest);
                
        if (! $this->fileExists($source)) {
            throw new FileManagerException(__("The source ':source' does not exists.",['source'=>$source]));
        }
        if ($this->fileExists($dest)) {
            throw new FileManagerException(__("The destination ':dest' exists.",['dest'=>$dest]));
        }
        if (! copy($source, $dest) || ! file_exists($dest)) {
            throw new FileManagerException(__("Couldn't copy ':source' to ':dest'.",['source'=>$source,'dest'=>$dest]));
        }
    }

    /**
     * Moves the file from source to dest
     * @param string $source
     * @param string $dest
     * @throws FileManagerException
     */
    public function moveFile(string $source, string $dest): void
    {
        $source = $this->getAbsolutePath($source);
        $dest = $this->getAbsolutePath($dest);
        
        if (! $this->fileExists($source)) {
            throw new FileManagerException(__("The source ':source' does not exists.",['source'=>$source,'dest'=>$dest]));
        }
        if ($this->fileExists($dest)) {
            throw new FileManagerException(__("The destination ':dest' exists.",['source'=>$source,'dest'=>$dest]));
        }
        if (! rename($source, $dest)) {
            copy($source, $dest);
            unlink($source);
        }
        if (file_exists($source)) {
            throw new FileManagerException(__("Couldn't move ':source' to ':dest'.",['source'=>$source,'dest'=>$dest]));
          //  unlink($dest);
        }
    }
       
    /**
     * Returns if the two files $test1 and $test2 are equal
     *
     * @param string $test1
     * @param string $test2
     * @param int $paranoia
     *            The level or paranoia (ignored at the moment)
     * @return boolean
     */
    public function filesEqual(string $test1, string $test2, int $paranoia = 2): bool
    {
        return md5_file($test1) === md5_file($test2);
    }
 
    /**
     * Adds another filehandler (a descendant of file) to the handler array
     * @param string $mime The associated mime
     * @param string $handler The class name of the handler
     */
    public function add_mime_handler(string $mime,string $handler) {
        $this->mime_associations[$mime] = $handler;
    }
    
    /**
     * Clears the handler array (for testing purposes normally)
     */
    public function clear_mime_handler() {
        $this->mime_associations = [];
    }

    /**
     * Gets the propriate handler object to the given mime
     * @param string $mime The mime the handler is needed for
     */
    public function get_file_handler_to_mime(string $mime) {
        $class = (isset($this->mime_associations[$mime])?$this->mime_associations[$mime]:(file::class));
        $result = new $class();
        return $result;
    }
    
    /**
     * Simplification that we get the appropriate handler to a given file
     * It just looks up the mime and creates the appropriate handler
     * @param string $file
     * @return unknown
     */
    public function get_file_handler_to_file(string $file) {
        return $this->get_file_handler_to_mime($this->get_file_mime($file));
    }
 
    /**
     * Returns the fileobject that matches the given parameter
     * @param $file
     *    file: trivial, just return
     *    link: return the target
     *    string: Check if this is the path to a
     *      file: return the correspending file object
     *      link: return the correspending file object of the target
     *     if not treat string as hash and return the file
     *     int: Treat int as id of the file and return it
     * @return file|null The found file or null if none was found
     * @throws FileManagerException if a wrong parameter was passed
     */
    public function GetFile($file) {
       if (is_a($file,file::class)) {
           return $file;
       } else if (is_a($file,link::class)) {
           return $file->target;
       } else if (is_string($file)) {
           if (strpos($file,DIRECTORY_SEPARATOR) !== false) {
            return $this->ResolvePath($file);
          } else {
            return $this->ResolveHash($file);
          } 
       } else if (is_int($file)) {
        
       } else {
          throw new FileManagerException("Can't resolve the given parameter to a file");
       }
    }
 
    /**
     * If $path is a link then return the target otherwise the file
     * @param $path string The location of the file or a link to a file
     * @return file A file object 
     */
    private function resolvePath(string $path) {
        $path = $this->normalizeFile($path);
        if (strpos($path,$this->get_media_dir()) === false) {
            $path = $this->get_media_dir().DIRECTORY_SEPARATOR.$path;
        }
        if (!file_exists($path)) {                
          return null;
        }
        if (is_link($path)) {
           $target = readlink($path);
           if (strpos($target,'.') !== false) {
             $path = pathinfo($path,PATHINFO_DIRNAME).DIRECTORY_SEPARATOR.$target;
           } else {
             $path = $target;
           } 
       }
       $path = $this->normalizeFile($path);       
       return file::search()->where('full_path',$path)->load_if_exists();
    }
 
    /**
     * Return the fileobject to this hash (or null if none exists)
     * @param $hash string the hash of the file
     * @return file|null the fileobject to this hash or null if not found
     */
    private function ResolveHash(string $hash) {
       file::search()->where('sha1_hash',$hash)->load_if_exists();
    } 
 
    public function GetLinkobjectsToFile($file) {
      $file = $this->GetFile($file);
      $result = link::search()->where('target',$file)->get();
      return $result;
    }
 
    public function GetLinksToFile($file) {
      $link_objects = $this->GetLinkobjectsToFile($file);
      $result = []; 
      foreach ($link_objects as $link) {
        $result[] = $link->full_path;
      }
      return $result;
    } 
}
