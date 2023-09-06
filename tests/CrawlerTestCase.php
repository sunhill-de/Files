<?php

namespace Sunhill\Files\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Sunhill\Basic\Tests\SunhillOrchestraTestCase;

class CrawlerTestCase extends SunhillOrchestraTestCase
{
    
    protected function getPackageProviders($app)
    {
        return [
            SunhillBasicServiceProvider::class,
            SunhillServiceProvider::class,
        ];
    }
    
}
