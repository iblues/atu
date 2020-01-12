<?php


namespace Iblues\AnnotationTestUnit\Libs;


class ApiTestFactory
{

    public $debug = 1;

    public function __construct($param)
    {

        $response = $this->json('GET', '/api/admin/admin/nav');
        $response
            ->assertStatus(200)
            ->assertJson([
                'code' => true,
            ]);

        dump($param);
    }
}