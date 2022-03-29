<?php

namespace Sunhill\Files;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Sunhill\ORM\Facades\Classes;

use Sunhill\Files\Console\Scan;
use Sunhill\Files\Console\Delete;

use Sunhill\Files\Managers\FileManager;
use Sunhill\Files\Managers\FileObjects;
use Sunhill\Files\Managers\Utils;

use Sunhill\Files\Objects\FileObject;
use Sunhill\Files\Objects\File;
use Sunhill\Files\Objects\Dir;
use Sunhill\Files\Objects\Link;
use Sunhill\Files\Objects\Mime;

class FilesServiceProvider extends ServiceProvider
{
    public function register()
    {
    }
    
    protected function registerClasses() {
        Classes::registerClass(Dir::class);
        Classes::registerClass(File::class);
        Classes::registerClass(FileObject::class);
        Classes::registerClass(Link::class);
        Classes::registerClass(Mime::class);
    }
    
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Scan::class,
                Delete::class            
            ]);
        }
    }
    
    public function boot()
    {
        $this->loadJSONTranslationsFrom(__DIR__.'/../resources/lang');
        $this->publishes([
            __DIR__.'/../config/crawler.php' => config_path('crawler.php'),
        ]);
        Schema::defaultStringLength(191);
        $this->app->singleton(FileManager::class, function () { return new FileManager(); } );
        $this->app->alias(FileManager::class,'filemanager');
        $this->app->singleton(Utils::class, function () { return new Utils(); } );
        $this->app->alias(Utils::class,'utils');
        $this->app->singleton(FileObjects::class, function () { return new FileObjects(); } );
        $this->app->alias(FileObjects::class,'fileobjects');
        
        $this->registerClasses();
        $this->registerCommands();
    }
}
