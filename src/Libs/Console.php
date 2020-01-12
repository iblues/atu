<?php


namespace Iblues\AnnotationTestUnit\Libs;


class Console
{
    static function dump($data)
    {
        echo $data, "\r\n";
    }

    static function info($data)
    {
        print("\033[1;33;40m $data \033[0m \r\n");
    }
}