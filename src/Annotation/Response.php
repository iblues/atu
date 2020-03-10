<?php

namespace Iblues\AnnotationTestUnit\Annotation;

use Iblues\AnnotationTestUnit\Libs\File;
use Iblues\AnnotationTestUnit\Libs\Param;
use Illuminate\Support\Arr;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Iblues\AnnotationTestUnit\Traits\ParseValue;


/**
 * 检查返回的断言. 需要返回json的. data.id=true,data.title="正则表达式*",
 * @link https://github.com/iblues/annotation-test-unit
 * @Annotation
 * @author Blues
 * Class Response
 * @package Iblues\AnnotationTestUnit
 */
class Response
{
    use ParseValue;
    protected $response;
    protected $expectHttpCode = null;
    protected $expectResponseJson = [];
    protected $asserts = [];
    protected $request = [];
    public $debugInfo = [];

    public function __construct($data = [])
    {
        if (!isset($data['value'])) {
            return;
        }
        $anns = $this->parseConstructValue($data);

        foreach ($anns as $an) {
            if (is_numeric($an)) {
                $this->expectHttpCode = $an;
                continue;
            }

            if ($an instanceof Assert) {
                $this->asserts[] = $an;
                continue;
            }

            if (is_array($an)) {
                $this->expectResponseJson[] = $an;
                continue;
            }

        }

    }

    /**
     * 获取数组格式的返回
     * @return Array
     * @author Blues
     */
    public function getJsonRespone()
    {
        $data = $this->response->decodeResponseJson();
        $data = json_decode(json_encode($data, JSON_UNESCAPED_UNICODE), 1);
        return $data;
    }

    public function setRespone($response)
    {
        $this->response = $response;
    }

    public function getRespone()
    {
        return $this->response;
    }

    /**
     * 输出返回结果.
     * @param $responseObj
     * @author Blues
     *
     */
    public function dumpResponse($responseObj)
    {
        $response = $responseObj->decodeResponseJson();


        //转成array
        $array = json_decode(json_encode($response), 1);

        //如果是500报错
        try {
            if ($responseObj['code'] == 500) {
                if (isset($response['message'])) {
                    $this->debugInfo['ErrorMsg'] = $response->message;
                }
                if (isset($response['data']) && isset($response['data']['message'])) {
                    $this->debugInfo['ErrorMsg'] = $response['data']['message'];
                }
                if (isset($response['data'])) {
                    $this->debugInfo['trace'] = $array['data'];
                }
            }
        } catch (\Exception $e) {

        }

        //如果层级太多了.大于16个. 就记录到日志中去
        if (is_array($array) && count($array, 1) > 2) {
            //创建日志

            File::clearFile(1);
            $file = File::saveFile('response', $response, true);
            $this->debugInfo['Response File'] = 'file://' . $file;

            $vl = json_encode($array, JSON_UNESCAPED_UNICODE);
            if (strlen($vl) > 60) {
                $vl = substr($vl, 0, 150) . '...';
            }
            $this->debugInfo['Response'] = $vl;
            ksort($this->debugInfo);

        } else {
            $vl = json_encode($array, JSON_UNESCAPED_UNICODE);
            $this->debugInfo['Response'] = $vl;
        }
    }

    public function assert($annotation, $request)
    {
        try {
            $responseJson = $this->response->decodeResponseJson();

            //将response信息写回debugInfo
            $this->dumpResponse($this->response);

            if ($this->expectHttpCode)
                $this->response->assertStatus($this->expectHttpCode);
            else {
                $this->response->assertOk();//为啥用这个. 因为这个会包含201,200
            }


            if ($this->expectResponseJson) {
                foreach ($this->expectResponseJson as $json) {
                    array_walk($json, [$this, 'walkParam']);
                    $this->response->assertAdvJson($json);
                }
            }

            if ($this->asserts) {
                foreach ($this->asserts as $assert) {
                    /**
                     * @var $assert Assert
                     */
                    $assert->handle($this->response, $request, $responseJson);
                }
            }

        } catch (\Exception $exception) {
            throw $exception;
        }
    }


    /**
     * 设置请求头. 方便读取
     * @param $request
     * @author Blues
     *
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * 判断是关联数组 还是顺序数组
     * @param array $arr
     * @return bool
     * @author Blues
     */
    protected function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }


}
