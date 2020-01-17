<?php


namespace Iblues\AnnotationTestUnit\Traits;


use Iblues\AnnotationTestUnit\Annotation\Request;
use Iblues\AnnotationTestUnit\Annotation\Response;

Trait ParseValue
{

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

}