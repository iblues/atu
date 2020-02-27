<?php


namespace Iblues\AnnotationTestUnit\Libs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Param
{

    /**
     *
     * @param string $key
     * @param $data = null 是读取
     * @return mixed
     * @throws \Exception
     * @author Blues
     */
    static public function param($key = '', $data = null)
    {
        static $cache = [];
        if ($data) {
            $data = json_decode(json_encode($data), 1);
            $cache[$key] = $data;
            return true;
        } else {
            if ($key) {
                $return = Arr::get($cache, $key, null);
                if (is_null($return)) {
                    throw new \Exception("Param : {$key} not exist");
                }
                return $return;
            } else {
                return $cache;
            }
        }

    }
}