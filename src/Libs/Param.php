<?php


namespace Iblues\AnnotationTestUnit\Libs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Param
{

    /**
     *
     * @param $key
     * @param $data = null 是读取
     * @return mixed
     * @author Blues
     *
     */
    static public function param($key = '', $data = null)
    {
        static $cache = [];
        if ($data) {
            $data = json_decode(json_encode($data), 1);
            $cache[$key] = $data;
            return true;
        } else {
            return $key ? Arr::get($cache, $key) : $cache;
        }

    }
}