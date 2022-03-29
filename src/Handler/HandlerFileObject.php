<?php

namespace Sunhill\Files\Handler;

use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Objects\File;
use Sunhill\Files\Objects\Mime;

/**
 * Checks if this file is already in the database. If yes, load it and fill some fields
 * If not, create the best fitting file object 
 * @author klaus
 * Depends: none
 * Modifies: 
 * - filestate.mime
 * - dbstate.isInDatabase
 * - dbstate.wasInDatabase
 * - dbstate.id
 * - file
 * - source
 * Condition: none
 */
class HandlerFileObject extends HandlerBase
{
 
    public static $prio = 5;
    
    /**
     * The signatures are taken from the getID3 project
     *
     * @var array
     */
    private static $header_infos = [
        'audio/flac' => '^fLaC',
        'audio/mpeg' => '^\\xFF[\\xE2-\\xE7\\xF2-\\xF7\\xFA-\\xFF][\\x00-\\x0B\\x10-\\x1B\\x20-\\x2B\\x30-\\x3B\\x40-\\x4B\\x50-\\x5B\\x60-\\x6B\\x70-\\x7B\\x80-\\x8B\\x90-\\x9B\\xA0-\\xAB\\xB0-\\xBB\\xC0-\\xCB\\xD0-\\xDB\\xE0-\\xEB\\xF0-\\xFB]',
        'image/jpeg' => '^\\xFF\\xD8\\xFF',
        'image/heic' => '\\x66\\x74\\x79\\x70\\x6d\\x69\\x66'
    ];
    
    private static $mime_to_ext = [
        'audio/flac' => 'flac',
        'audio/mpeg' => 'mp3',
        'image/jpeg' => 'jpg',
        'image/heic' => 'heic',
    ];
    
    function process(CrawlerDescriptor $descriptor)
    {
        if ($file = $this->searchHash($descriptor->filestate->sha1_hash,$descriptor)) {
            $this->alreadyInDatabase($descriptor,$file);
        } else {
            $this->notInDatabase($descriptor);
        }        
    }

    protected function searchHash($hash,$descriptor)
    {
        if ($result = File::search()->where('sha1_hash','=',$hash)->loadIfExists()) {
            $this->verboseinfo(" Hash already in database");
            return $result;
        } else {
            $this->verboseinfo(" Hash not in database");
            return false;
        }
    }
    
    protected function alreadyInDatabase(CrawlerDescriptor $descriptor, File $file)
    {
        $descriptor->file = $file;
        $descriptor->dbstate->isInDatabase = true;
        $descriptor->dbstate->wasInDatabase = true;
        $descriptor->dbstate->id = $file->getID();
        $descriptor->filestate->mime_str = $file->mime->mime;
        $descriptor->filestate->mime_obj = $file->mime;
    }
    
    protected function notInDatabase(CrawlerDescriptor $descriptor)
    {
        $descriptor->dbstate->isInDatabase = false;
        $descriptor->dbstate->wasInDatabase = false;
        $descriptor->dbstate->id = false;
        $descriptor->filestate->mime_str = $this->detectMime($descriptor->getCurrentLocation());
        $descriptor->filestate->mime_obj = $this->getMime($descriptor->filestate->mime_str);
        $descriptor->filestate->ext = $this->getExt($descriptor->source,$descriptor->filestate->mime_str);
        $this->createFileObject($descriptor); 
    }
        
    /**
     * @todo Add additonal detection here
     * @param string $source
     * @return Stringfalse
     */
    protected function detectMime(string $source): String
    {
        $sample = $this->get_header($source);
        foreach (static::$header_infos as $mime => $pattern) {
            if (preg_match('#' . $pattern . '#s', $sample)) {
                return $mime;
            }
        }
        return $this->get_mime_type($source);
    }
    
    protected function getMime(string $mime): Mime
    {
        $result = Mime::search()->where('mime','=',$mime)->loadIfExists();
        if ($result) {
            return $result;
        }
        $result = new Mime();
        list($main,$sub) = explode('/',$mime);
        $result->mimegroup = $main;
        $result->item = $sub;
        $result->default_ext = '';
        $result->commit();
        return $result;
    }
    
    private function get_header(string $path)
    {
        $fp = fopen($path, 'r');
        $sample = fread($fp, 32774);
        fclose($fp);
        return $sample;
    }
    
    /**
     * Ermittelt den angepassten Mime-Typ für die übergebene Datei
     *
     * @param string $fullpath
     *            Pfad und Dateiname der zu untersuchenden Datei
     * @return string Mimestring der Datei
     */
    public function get_mime_type(string $fullpath)
    {
        $file_mime = mime_content_type($fullpath);
        switch ($file_mime) {
            case 'application/octet-stream':
            case 'text/plain':
                return $this->get_extended_mime_type($fullpath);
            case 'image/heif':
                return 'image/heic';
            case 'audio/x-flac':
                return 'audio/flac';
            default:
                return $file_mime;
        }
    }
    
    /**
     * Wenn die oben stehende Funktion keinen Mime-Typ herausfindet, müssen schwerere Geschütze aufgefahren werden
     *
     * @param string $fullpath
     */
    private function get_extended_mime_type(string $fullpath)
    {
        $ext = strtolower(pathinfo($fullpath, PATHINFO_EXTENSION));
        if (($ext == 'heic') || ($ext == 'heif')) {
            return 'image/heic';
        }
        
        // Jetzt bleibt uns nur noch die Endung
        if (isset(self::$ext_to_mime[$ext])) {
            return self::$ext_to_mime[$ext];
        } else {
            return 'application/octet-stream';
        }
    }
    
    /**
     * Ermittelt eine standarisierte Dateierweiterung der übergebenen Datei
     *
     * @param string $fullpath
     * @return string Dateierweiterung der Datei
     */
    public function getExt(string $filename, string $mime_type)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (isset(self::$mime_to_ext[$mime_type])) {
            return self::$mime_to_ext[$mime_type];
        } else {
            return $ext;
        }
    }
    
    protected function createFileObject($descriptor)
    {
        switch ($descriptor->filestate->mime_str)
        {
            default:
                $descriptor->file = new File();
                break;
        }
        $descriptor->file->sha1_hash = $descriptor->filestate->sha1_hash;
        $descriptor->file->name = pathinfo($descriptor->source,PATHINFO_FILENAME);
        $descriptor->file->md5_hash = md5_file($descriptor->getCurrentLocation());
        $descriptor->file->size  = filesize($descriptor->getCurrentLocation());
        $descriptor->file->created = filectime($descriptor->getCurrentLocation());
        $descriptor->file->changed = filemtime($descriptor->getCurrentLocation());
        $descriptor->file->ext = $descriptor->filestate->ext;
        switch ($descriptor->command) {
            case 'scan':
                $descriptor->file->type = 'regular';
                break;
            case 'delete':
                $descriptor->file->type = 'deleted';
                break;
            case 'ignore':
                $descriptor->file->type = 'ignored';
                break;
        }
        $descriptor->file->mime = $descriptor->filestate->mime_obj;
    }
    
    function matches(CrawlerDescriptor $descriptor): Bool
    {
        return $descriptor->fileReadable();
    }
    
}
