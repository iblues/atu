<?php

namespace Iblues\AnnotationTestUnit\Annotation;

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
    protected $expectResponseJson;
    protected $asserts = [];
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
                $this->expectResponseJson = $an;
                continue;
            }

        }

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
        $response = $responseObj->getData();
        //如果是500报错
        if ($responseObj->getStatusCode() == 500) {
            if (property_exists($response, 'message')) {
                $this->debugInfo['ErrorMsg'] = $response->message;
            }
            if (property_exists($response, 'data') && property_exists($response->data, 'message')) {
                $this->debugInfo['ErrorMsg'] = $response->data->message;
            }
        }

        $vl = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $array = json_decode($vl, 1);
        //如果层级太多了.大于16个. 就记录到日志中去
        if (count($array, 1) > 16) {
            //创建日志
            $filePath = storage_path('testResponse');
            if (!file_exists($filePath)) {
                mkdir($filePath);
            }
            $this->clearResponeFile();
            //创建git忽略
            if (!file_exists($filePath . '/.gitignore')) {
                file_put_contents($filePath . '/.gitignore', "*\r\n!.gitignore");
            }

            $file = $filePath . '/' . $this->msectime() . '.json';
            if (file_put_contents($file, $vl)) {
                $this->debugInfo['ResponseFile'] = 'file://' . $file;
            }

        } else {
            $vl = json_decode($vl);
            $vl = json_encode($vl, JSON_UNESCAPED_UNICODE);
            $this->debugInfo['Response'] = $vl;
        }
    }

    /**
     * remove 1h ago files
     * @author Blues
     */
    public function clearResponeFile()
    {
        $filePath = storage_path('testResponse');
        $list = scandir($filePath);
        foreach ($list as $file) {
            $name = pathinfo($file);
            if ($name['filename'] && $name['filename'] != '.'
                && ($name['filename'] < (time() - 3600) . '000')) {
                unlink($filePath . '/' . $file);
            }
        }
    }

    public function assert($annotation, $request)
    {
        try {
            $responseJson = $this->response->getData();
            if ($this->expectHttpCode)
                $this->response->assertStatus($this->expectHttpCode);
            else {
                $this->response->assertOk();//为啥用这个. 因为这个会包含201,200
            }


            if ($this->expectResponseJson) {
                $this->response->assertJson($this->expectResponseJson);
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
            $this->dumpResponse($this->response);
            $exception->debugInfo = $this->debugInfo;
            throw $exception;
        }
    }


    /**
     * 返回毫秒
     * @return float
     * @author Blues
     *
     */
    protected function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
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
