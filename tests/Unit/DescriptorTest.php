<?php
namespace Sunhill\Files\Tests\Unit;

use Sunhill\Basic\Tests\SunhillOrchestraTestCase;
use Sunhill\Basic\Utils\DescriptorException;
use Sunhill\Files\CrawlerDescriptor;
use Sunhill\Files\Tests\CreatesApplication;
use Sunhill\Basic\Utils\Descriptor;

class DescriptorTest extends SunhillOrchestraTestCase
{
  
      use CreatesApplication;
      
      /**
       * @dataProvider HelperProvider
       */
      public function testHelpers($fields,$method,$expect)
      {
        if ($expect === 'except') {  
          $this->expectException(DescriptorException::class);  
        }
        $test = new CrawlerDescriptor();
        $test->filestate = new Descriptor();
        $test->file = new Descriptor();
        $test->dbstate = new Descriptor();
        $test->target = new Descriptor();
        if (is_array($fields)) {
            foreach ($fields as $key => $value) {
                if (strpos($key,'->')) {
                    list($mainkey,$subkey) = explode('->',$key);
                    $test->$mainkey->$subkey = $value;
                } else {
                    $test->$key = $value;
                }
            }
        }
        $this->assertEquals($expect,$test->$method());
      }
  
      public function HelperProvider()
      {
        return [
          [null,'alreadyInDatabase','except'],
          [['dbstate->wasInDatabase'=>true],'alreadyInDatabase',true],
          [['filestate->readable'=>true],'fileProcessable',true], 
        ];
      }
}
