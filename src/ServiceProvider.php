<?php


namespace Iblues\AnnotationTestUnit;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public function boot()
    {
        $path = dirname(__DIR__);
        $this->publishes([
            $path . '/src/Publish/tests' => base_path('tests/'),
        ], 'ATU');
    }
}