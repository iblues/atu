<?php

namespace Iblues\AnnotationTestUnit\Libs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class LogAssert
{

    protected $data = [];

    /**
     *
     * @param string $key
     * @param $data = null 是读取
     * @return mixed
     * @throws \Exception
     * @author Blues
     */
    public function log($data = null)
    {

        $level = $data->level;
        $message = $data->message;
        echo $content = json_encode($data->context, JSON_UNESCAPED_UNICODE);

        $this->data[] = ['level' => $level, 'message' => $message, 'content' => $content];
    }

    function search($keyword, $level = null)
    {
        $data = $this->data;
        if ($level) {
            $data = Arr::where($data, function ($value, $key) use ($level) {
                return $value['level'] == $level;
            });
        }

        foreach ($data as $v) {

            if (stripos($v['message'], $keyword) !== false) {
                return true;
            }
            if (stripos($v['content'], $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    function clear()
    {
        $this->data = [];
    }
}