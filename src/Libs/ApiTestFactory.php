<?php


namespace Iblues\AnnotationTestUnit\Libs;


use Iblues\AnnotationTestUnit\Annotation\Api;
use Iblues\AnnotationTestUnit\Annotation\TestApi;
use Iblues\AnnotationTestUnit\Libs\Php2Curl;
use Illuminate\Foundation\Testing\TestResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ApiTestFactory
{

    public $debug = false;

    public $url = '';
    public $method = 'GET';
    public $fileLine = null;
    public $methodPath = null;
    public $testClass = null;
    protected $number = 0;//Atu计数
    protected $request;

    public function __construct($testClass, $param, $filter = null)
    {
        $this->testClass = $testClass;
        $this->url = $param['url'];
        $this->method = $param['httpMethod'];
        $this->fileLine = $methodLine = 'file://' . $param['classPath'] . ':' . $param['methodStartLine'];
        $this->debug = $filter['debug'] ?? false;
        $this->methodPath = $param['path'];

        foreach ($param['annotation'] as $annotation) {
            $this->walkAnnotation($annotation, $filter);
        }
    }

    public function walkAnnotation(Api $annotation, $filter = [])
    {
        $testClass = $this->testClass;

        if ($annotation->now) {
            //如果是开启了当前测试. 提醒下哪些开了的 方便随时关闭
            dump('@ATU\Now enable in ' . $this->methodPath . '(' . $this->fileLine . ")");

        }
        try {
            if (@$filter['now'] && !$annotation->isNow()) {
                return;
            }

            //如果不满足tag,就跳过.
            if (!$annotation->inTag(@$filter['tag'])) {
                return;
            }

            $this->number++;

            $testClass->refresh();

            //执行$before相关函数
            $annotation->handleBofore($testClass);

            //计时
            $startTime = $this->msectime();
            $request = $annotation->handleRequest($testClass, $this->method, $this->url);
            $response = $annotation->handleResponse($testClass, $annotation, $request);

//dump($response);
            //处理跟返回参数无关的assert.比如数据库
            $annotation->handleAssert($testClass, $annotation, $request, $response);

            //登录验证参数,给curl用
            $loginUser = $testClass->loginUser;

            //处理Authorization
            $request = $this->handelHeaderAuthorization($testClass, $request);

            //如果有@ATU\debug()
            if ($annotation->debug || $this->debug)
                $this->debugInfo($annotation, $request, $startTime, $loginUser, 0);

        } catch (\Exception $e) {

            //处理Authorization
            $request = $this->handelHeaderAuthorization($testClass, $request ?? []);

            $this->debugInfo($annotation, $request ?? [], $startTime ?? 0, $loginUser ?? null, 1);
            throw $e;
        }
    }

    protected function handelHeaderAuthorization($testClass, $request)
    {
        $loginUser = $testClass->loginUser;
        if ($loginUser) {
            $token = \Auth::guard($testClass->guard ?? 'api')->login($loginUser);
            $request['headers']['Authorization'] = 'bearer ' . $token;
            return $request;
        }
        return $request;
    }

    protected function debugInfo($annotation, $request, $startTime = 0, $loginUser = null, $error = true)
    {
        $debugInfo = [];
        /**
         * @var $annotation Api
         */
        $debugInfo = $annotation->getResponeDebugInfo();
        if ($error)
            Console::error(' ----------------------------------------- DEBUG -----------------------------------------');
        else
            Console::info(' ----------------------------------------- INFO ------------------------------------------');
        if ($annotation->title) {
            $this->dump('Title', $annotation->title);
        }
        $this->dump('Code', "{$this->methodPath} ( {$this->fileLine} )");
        $this->dump('URL', $this->method . '  -  ' . @$request['url'] ?? '');
        if ($loginUser) {
            $this->dump('Login ID', $loginUser->id);
        }
        $this->dump('Request', json_encode($request['request'] ?? null, JSON_UNESCAPED_UNICODE));
        $this->dump('CURL', $this->toCurlCommand($request, $loginUser));
        if ($startTime)
            $this->dump('Time', (($this->msectime() - $startTime) / 1000) . 's');
        foreach ($debugInfo as $key => $info) {
            if ($error && in_array($key, ['ErrorMsg', 'Response'])) {
                $this->dump($key, $info, 1);
            } else {
                $this->dump($key, $info);
            }
        }


        if ($error)
            Console::error(' ----------------------------------------- END -------------------------------------------');
        else
            Console::info(' ----------------------------------------- END -------------------------------------------');

        //即时输出到命令行
        ob_flush();

    }

    protected function toCurlCommand($request, $loginUser)
    {
        $get = [];
        $request['method'] = $request['method'] ?? '';

        $post = $request['request'] ?? [];
        if (@$request['url'][0] !== '/') {
            @$request['url'] = '/' . $request['url'];
        }
        $server = ['REQUEST_METHOD' => $request['method'], 'SERVER_NAME' => $_ENV['APP_URL'], 'REQUEST_URI' => $request['url']];
        $headers = $request['headers'] ?? [];
        $headers['content-type'] = 'application/javascript';
        $phpInput = [];
        $curl = (new Php2Curl($get, $post, [], $server, $headers, $phpInput))->doAll();
        return 'file://' . File::saveFile('CURL', $curl);
    }

    protected function dump($key, $val, $error = false)
    {
        $strings = explode("\n", $val);
        foreach ($strings as $k => $string) {
            if ($k > 0) {
                $strings[$k] = '                        ' . $string;
            }
        }
        $stirng = implode("\n", $strings);
        $stirng = ' - ' . str_pad($key, 15, ' ', STR_PAD_RIGHT) . ":   {$stirng}";
        if ($error) {
            Console::error($stirng);
        } else {
            Console::dump($stirng);
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
     * 返回执行了多少个ATU;
     * @author Blues
     *
     */
    public function getNumber()
    {
        return $this->number;
    }
}
