<?php


namespace Iblues\AnnotationTestUnit;

use Iblues\AnnotationTestUnit\Assert\AssertAdvJson;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Testing\TestResponse as TestResponseLV7;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public function boot()
    {
        //兼容Laravel7
        $TestResponse = class_exists(TestResponse::class) ? TestResponse::class : TestResponseLV7::class;
        $TestResponse::macro('assertAdvJson', function ($data) {
            AssertAdvJson::assert($data, $this->decodeResponseJson(), false, $this->assertJsonMessage($data));
            return $this;
        });


        $path = dirname(__DIR__);
        $this->publishes([
            $path . '/src/Publish/tests' => base_path('tests/'),
        ], 'ATU');
    }
}