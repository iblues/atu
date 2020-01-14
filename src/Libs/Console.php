<?php


namespace Iblues\AnnotationTestUnit\Libs;


class Console
{
    static function dump($data)
    {
        print("\033[0;31;0m $data \033[0m \r\n");
    }

    static function info($data)
    {
        print("\033[1;33;40m $data \033[0m \r\n");
    }

    static function error($data)
    {
        print("\033[0;31;48m $data \033[0m \r\n");
    }
}