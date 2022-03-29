<?php 

namespace Sunhill\Files\Facades;

use Illuminate\Support\Facades\Facade;

class FileObjects extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'fileobjects';
    }
}
