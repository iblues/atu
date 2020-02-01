<?php


namespace Iblues\AnnotationTestUnit\Traits;


use Iblues\AnnotationTestUnit\Annotation\GetParam;
use Iblues\AnnotationTestUnit\Annotation\GetRequest;
use Iblues\AnnotationTestUnit\Annotation\GetResponse;
use Iblues\AnnotationTestUnit\Annotation\Request;
use Iblues\AnnotationTestUnit\Annotation\Response;
use Illuminate\Support\Arr;

Trait ParseValue
{

    /**
     * 处理phpdoc传入的变量.
     * @param $data
     * @return array|mixed|void
     * @author Blues
     *
     */
    protected function parseConstructValue($data)
    {
        if (!isset($data['value'])) {
            return;
        }
        $value = $data['value'];
        //不是数字的.说明只传了一个数字或者字符串进来
        if (!is_array($value)) {
            $value = [$value];
        }
        foreach ($value as $key => $val) {
            //如果key不是数字.或者是数组但是大于30的. 就认为他是数组   小于30的请把after参数放前面吧
            if (!is_numeric($key) or (is_numeric($key) && $key > 30)) {
                $value[] = [$key => $val];
                unset($value[$key]);
            }
        }

        return $value;
    }


    /**
     * 把对象处理为字符
     * @param $value
     * @author Blues
     */
    protected function walkParam(&$value)
    {
        if (is_array($value)) {
            array_walk($value, [$this, 'walkParam']);
        } else {
            if (gettype($value) == 'object') {

                if ($value instanceof GetResponse) {
                    $value = $value->handel($this->response->getJsonRespone());
                } else if ($value instanceof GetRequest) {
                    $value = $value->handel($this->request['request']);
                } else if ($value instanceof GetParam) {
                    $value = $value->handel();
                }
            }
        }
    }

}