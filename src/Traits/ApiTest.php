<?php

namespace Iblues\AnnotationTestUnit\Traits;

use Iblues\AnnotationTestUnit\Libs\Annotation;
use Iblues\AnnotationTestUnit\Libs\ApiTestFactory;
use Iblues\AnnotationTestUnit\Libs\Routes;
use Larfree\Libs\Swagger;

trait ApiTest
{

    function doNow()
    {
        $todoList = Annotation::getApiTest(1);
        foreach ($todoList as $todo) {
            $return = new ApiTestFactory($this, $todo);
        }
    }

    function doAll()
    {
        $todoList = Annotation::getApiTest();
        foreach ($todoList as $todo) {
            $return = new ApiTestFactory($this, $todo);
        }
    }
}


