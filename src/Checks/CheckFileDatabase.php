<?php

namespace Sunhill\Files\Checks;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Sunhill\Basic\Checker\Checker;

class CheckFileDatabase extends Checker
{

    protected $repair;
    
    public function checkMissingDBEntries(bool $repair)
    {
        $this->repair = $repair;
        $this->scanDir(config('crawler.media_dir'));
        return $this->createResult('OK','checkMissingDBEntries');
    }
    

    private function scanDir(string $dirname,$depth=0)
    {
        $dir = dir($dirname);
        while (false !== ($entry = $dir->read())) {
            if (($entry == '.') || ($entry == '..')) {
                continue;
            }
            
            $filename = $dirname.'/'.$entry;
            
            if (is_dir($filename)){
                $this->handleDir($filename);
            } else if (is_link($filename)) {
                $this->handleLink($filename);
            } else if (is_file($filename)) {
                $this->handleFile($filename);
            }
        }
        $dir->close();
    }
    
    private function searchDir($path)
    {
        $result = DB::table('dirs')->where('full_path',$path)->first();
        if ($result) {
            return $result->id;
        } else {
            return 0;
        }
    }
    
    private function removePrefix($pathname)
    {
        if (strpos($pathname,config('crawler.media_dir')) !== false) {
            return substr($pathname,strlen(config('crawler.media_dir')));
        }
    }
    
    private function handleDir($dirname)
    {
        $internal = Str::finish($this->removePrefix($dirname),"/");
        $this->debug("Entering directory '$dirname' internal '$internal'");
        if (!$this->searchDir($internal)) {
            $this->debug(" Directory not found");
            if ($this->repair) {
                $this->debug(" going to repair");
                $parts = explode("/",$internal);
                array_pop($parts);
                $dir = array_pop($parts);
                $parts = implode("/",$parts);
                DB::table('dirs')->insert(
                    [
                        'full_path' => Str::finish($internal,"/"),
                        'name'=>$dir,
                        'parent_dir'=>$this->searchDir(Str::finish($parts,"/"))                        
                    ]
                );
            }
        }
        $this->scanDir($dirname);
    }
    
    private function handleLink($linkname)
    {
    }
    
    private function handleFile($filename)
    {
    }
    
    
}