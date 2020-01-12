<?php

namespace Iblues\AnnotationTestUnit\Traits;

use Iblues\AnnotationTestUnit\Libs\Annotation;
use Iblues\AnnotationTestUnit\Libs\ApiTestFactory;
use Iblues\AnnotationTestUnit\Libs\Routes;
use Larfree\Libs\Swagger;

trait ApiTest
{
    function doTest()
    {
        $response = $this->json('GET', '/api/admin/admin/nav');
        $response
            ->assertStatus(200)
            ->assertJson([
                'code' => true,
            ]);
    }


    function doNow()
    {
        $todoList = Annotation::getApiTest(1);
        foreach ($todoList as $todo) {
            $return = new ApiTestFactory($this, $todo);
        }
    }

}


