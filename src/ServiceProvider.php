<?php


namespace Iblues\AnnotationTestUnit;

use Iblues\AnnotationTestUnit\Assert\AssertAdvJson;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse as TestResponseLV7;
use Iblues\AnnotationTestUnit\Libs\LogAssert;

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

        //Illuminate\Log\Events\MessageLogged
        Event::listen(MessageLogged::class, function ($data) {
            app(LogAssert::class)->log($data);
        });

        //单例
        $this->app->singleton(LogAssert::class, function ($app) {
            return new LogAssert();
        });

        $path = dirname(__DIR__);
        $this->publishes([
            $path . '/src/Publish/tests' => base_path('tests/'),
        ], 'ATU');
    }
}