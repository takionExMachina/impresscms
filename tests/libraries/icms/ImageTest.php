<?php

namespace ImpressCMS\Tests\Libraries\ICMS;

use icms;
use ImpressCMS\Core\Models\AbstractExtendedHandler;
use ImpressCMS\Core\Models\AbstractExtendedModel;
use PHPUnit\Framework\TestCase;

/**
* @backupGlobals disabled
* @backupStaticAttributes disabled
*/

class ImageTest extends TestCase {

    /**
     * Test if is available
     */
    public function testAvailability() {
        foreach ([
                'icms_image_category_Handler' => AbstractExtendedHandler::class,
                'icms_image_category_Object' => AbstractExtendedModel::class,
                'icms_image_set_Handler' => AbstractExtendedHandler::class,
                'icms_image_set_Object' => AbstractExtendedModel::class,
                'icms_image_Handler' => AbstractExtendedHandler::class,
                'icms_image_Object' => AbstractExtendedModel::class,
                'icms_image_body_Handler' => AbstractExtendedHandler::class,
                'icms_image_body_Object' => AbstractExtendedModel::class
            ] as $class => $must_be_instance_of) {
                $this->assertTrue(class_exists($class, true), $class . " class doesn't exist");
            if ($must_be_instance_of !== null) {
                $instance = $this->getMockBuilder($class)
                    ->disableOriginalConstructor()
                    ->getMock();
                $this->assertInstanceOf($must_be_instance_of, $instance, $class . ' is not instanceof ' . $must_be_instance_of);
            }
        }
    }

    /**
     * Tests image body functionality
     */
    public function testImageBody() {
        $image_handler = icms::handler('icms_image');
        $image = $image_handler->create();
        $this->assertTrue(property_exists($image, 'image_body'), 'icms_image_Object doesn\'t have image_body property');
        $test_var = sha1(microtime(true));
        $image->image_body = $test_var;
        $this->assertSame($test_var, $image->getVar('image_body'), 'getVar for icms_image_Object doesn\'t work as expected (I)');
        $image->image_body = null;
        $image->setVar('image_body', $test_var);
        $this->assertSame($test_var, $image->getVar('image_body'), 'getVar for icms_image_Object doesn\'t work as expected (II)');
    }

}