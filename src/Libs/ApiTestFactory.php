<?php


namespace Iblues\AnnotationTestUnit\Libs;


use Iblues\AnnotationTestUnit\Annotation\Api;
use Iblues\AnnotationTestUnit\Annotation\TestApi;
use Illuminate\Support\Arr;

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
        //代表是其他函数call的
        $call = Arr::get($filter, 'call', false);

        if ($annotation->now && !$call) {
            //如果是开启了当前测试. 提醒下哪些开了的 方便随时关闭
            dump('@ATU\Now enable in ' . $this->methodPath . '(' . $this->fileLine . ")");
        }
        try {

            //@ATU\ignore
            if ($annotation->isIgnore()) {
                dump('@ATU\Ignore enable in ' . $this->methodPath . '(' . $this->fileLine . ")");
                return;
            }

            //如果不满足tag,就跳过.
            if (!$annotation->inTag(@$filter['tag'])) {
                return;
            }

            //@ATU\now
            if (@$filter['now'] && !$annotation->isNow()) {
                return;
            }

            $this->number++;

            //刷新事务
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
            $annotation->handleAfter($testClass, $annotation, $request, $response);

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


        $sql = $annotation->getDataBaseLog();
        $telescope = $this->telescope($sql);

        $sql = 'file://' . File::saveFile('SQL', implode("\r\n", $sql), false, '.sql');
        $this->dump('SQL', $sql);


        //如果启动了telescope
        if ($telescope)
            $this->dump('Telescope', $telescope);


        foreach ($debugInfo as $key => $info) {

            if ($error && in_array($key, ['ErrorMsg', 'Response'])) {
                $this->dump($key, $info, 1);
            } else if ($key == 'trace') {
                Console::error(' ----------------------------------------- Error Trace -------------------------------------------');
                if (isset($info['file'])) {
                    Console::error("file://{$info['file']}:{$info['line']}");
                }
                if (isset($info['trace'])) {
                    foreach ($info['trace'] as $trace) {
                        $class = '';
                        $file = '';
                        if (isset($trace['file'])) {
                            $file = "file://{$trace['file']}:{$trace['line']}";
                        }
                        if (isset($trace['class'])) {
                            $class = $trace['class'] . ' ->';
                        }
                        Console::error("{$file}  ({$class}{$trace['function']})");
                    }
                }
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
        $headers['content-type'] = 'application/json';
        $phpInput = [];
        $curl = (new Php2Curl($get, $post, [], $server, $headers, $phpInput))->doAll();
        return 'file://' . File::saveFile('CURL', $curl, false, '.sh');
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

    /**
     * 由于正常启用了事务回滚的.所以把相关部分重新插入
     * @author Blues
     *
     */
    protected function telescope($sqls)
    {
        //这里启用事务的话. 会把上下文的引起问题. 先暂停
        return false;
        //临时先关闭事务. 让其写入数据库
        \DB::rollBack();
        foreach ($sqls as $sql) {
            $logSql = [];
            //就认为是telescope启用了
            if (substr($sql, 0, '31') == 'insert into `telescope_entries`'
                || substr($sql, 0, '36') == 'insert into `telescope_entries_tags`'
            ) {
                $match = [];
                preg_match('/\"(.*?)\"/i', $sql, $match);
                \DB::insert($sql);
            }
        }
        \DB::beginTransaction();
        $id = $match['1'] ?? null;
        if ($id) {
            return $url = config('app.url') . '/telescope/requests/' . $id;
        }

    }
}
