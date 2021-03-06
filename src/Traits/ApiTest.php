<?php

namespace Iblues\AnnotationTestUnit\Traits;

use EasyWeChat\Kernel\Support\Arr;
use Iblues\AnnotationTestUnit\Libs\Annotation;
use Iblues\AnnotationTestUnit\Libs\Console;
use Iblues\AnnotationTestUnit\Libs\LogAssert;
use Iblues\AnnotationTestUnit\Libs\Param;
use Iblues\AnnotationTestUnit\Libs\ApiTestFactory;
use Iblues\AnnotationTestUnit\Libs\Routes;
use Tests\Feature\AnnotationTest;
use Iblues\AnnotationTestUnit\Assert\AssertAdvJson;

trait ApiTest
{
    /**
     * 用来存储param相关变量
     * @var array
     */
    public $param = [];
    public $loginUser = null;
    protected $todoList = [];
    protected $insertSql = [];


    protected function getToDoList()
    {
        $cache = $this->cache ?? false;
        //避免二次读取
        if (!$this->todoList)
            $this->todoList = Annotation::getApiTest(['whiteList' => $this->whiteList ?? [], 'blackList' => $this->blackList ?? []], $cache);

        //等于是深度clone. 如果不clone 在before互相调用的时候. 引用会出问题.
        return unserialize(serialize($this->todoList));
    }

    /**
     * 测试带有@ATU\Api和@ATU\Now注解的
     * @param array $filter
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \ReflectionException
     * @author Blues
     */
    protected function doNow($filter = [])
    {
        $number = 0;
        $todoList = $this->getToDoList();

        foreach ($todoList as $todo) {
            $ATU = new ApiTestFactory($this, $todo, ['tag' => $filter['tag'] ?? null, 'now' => 1, 'debug' => $this->debug ?? false]);
            $number += $ATU->getNumber();
        }
        Console::info('Total ATU: ' . $number);
        if ($number == 0) {
            //避免没有任何断言的报错
            $this->assertTrue(true);
        }
        ob_flush();
    }

    /**
     * 测试带有@ATU\Api()注解的
     * @param array $filter
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \ReflectionException
     * @author Blues
     */
    protected function doAll($filter = [])
    {

        $call = Arr::get($filter, 'call', false);

        $number = 0;
        $todoList = $this->getToDoList();

        foreach ($todoList as $todo) {
            $ATU = new ApiTestFactory($this, $todo, ['tag' => $filter['tag'] ?? null, 'debug' => $this->debug ?? false, 'call' => $call]);
            $number += $ATU->getNumber();
        }

        //call的不输出这个了
        if (!$call) {
            Console::info('Total ATU: ' . $number);
        }
        ob_flush();
    }

    /**
     * @param $name
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @author Blues
     */
    public function callATU($name)
    {
        $iso = $this->isolateApp;
        $this->isolateApp = false;
        $this->doAll(['tag' => $name, 'call' => $name]);
        $this->isolateApp = $iso;
    }


    /**
     * 读取所有带有@ATU\Api主键的,看是否有对应的路由匹配. 如果没有匹配路由就报错. 可用@ignore暂时忽略
     * @author Blues
     */
    protected function checkRouter()
    {
        Routes::checkAllRoute($this);
    }


    /**
     * 模拟登录. 如果没有会出错!
     * must have it!
     * @param bool $id
     * @return AnnotationTest
     * @throws \Exception
     * @author Blues
     */
    public function login($id = true)
    {
        $driver = $this->guard ?? 'api';
        if ($id == false) {
            $id = $this->app['auth']->guard($driver)->id();
            if ($id) {
                $this->app['auth']->guard($driver)->logout();
                $this->app['auth']->shouldUse($driver);
            }
            return $this;
        }
        if (property_exists($this, 'userModel')) {
            $class = $class = $this->userModel;
        } else {
            $class = class_exists(\App\Models\Common\CommonUser::class) ? \App\Models\Common\CommonUser::class : \App\User::class;
        }

        if (!class_exists($class)) {
            throw new \Exception("$class not exist, Please use \$this->userModel override it");
        }
        $user = $id ? $class::find($id) : $class::first();
        $this->loginUser = $user;
        return $this->actingAs($user, $driver);
    }


    /**
     * 设置变量 通过getParam()调用
     * @param $key
     * @param $data
     * @return boolean|array
     * @throws \Exception
     * @author Blues
     *
     */
    public function setParam($key, $data)
    {
        return Param::Param($key, $data);
    }

    /**
     * 设置变量 通过setParam()设置
     * @param $key
     * @return boolean|array
     * @throws \Exception
     * @author Blues
     *
     */
    public function getParam($key)
    {
        return Param::Param($key);
    }

    /**
     * 由于有些断言是私有方法. 开个接口来调
     * @param $func
     * @param $param
     * @return mixed
     * @author Blues
     *
     */
    public function callProtectedFunction($func, $param)
    {
        return call_user_func_array([$this, $func], $param);
    }


    protected function getRandPhone()
    {
        return '1' . rand(1300000000, 9999999999);
    }

    protected function getRandEmail()
    {
        return str_random(10) . '@gmail.com';
    }

    protected function getPassword()
    {
        return bcrypt('secret');
    }


    /**
     * 刷新app状态. 以及测试的回退. 隔离测试变量
     * @author Blues
     *
     */
    public function refresh()
    {
        $isolateApp = $this->isolateApp ?? true;
        if ($isolateApp) {
            $this->tearDown();
            $this->setUp();
            app(LogAssert::class)->clear();
        }
    }

    /**
     * 测试数据创建器 updateOrCreate
     * @param string $model
     * @param array $data
     * @param null|array $where
     * @throws \Exception
     * @author Blues
     */
    public function create(string $model, array $data, $where = null)
    {
        if (!class_exists($model)) {
            throw  new \Exception("$model not exist, Please sure use {$model} in controller file");
        }
        $model = new $model();
        $key = $model->getKeyName();
        //有主键
        if (key_exists($key, $data)) {
            $model::updateOrInsert([$key => $data[$key]], $data);
            $result = $model::where($key, $data[$key])->first();
        } else {
            if ($where) {
                $result = $model::updateOrCreate($where, $data);
            } else {
                $result = $model::create($data);
            }
        }
        $name = basename(str_ireplace('\\', '/', get_class($model)));
        $this->setParam($name, $result->toArray());
    }

    /**
     * 删除指定数据
     * @param string $model
     * @param array $where
     * @throws \Exception
     * @author Blues
     *
     */
    public function delete(string $model, array $where)
    {
        if (!class_exists($model)) {
            throw  new \Exception("$model not exist, Please sure use {$model} in controller file");
        }
        $model = new $model();
        $model->where($where)->delete();
    }


    /**
     * 设置最后要写入数据库的sql.不受事务影响
     * @author Blues
     *
     */
    public function setInsertSql($sql)
    {
        $this->insertSql[] = $sql;
    }


    /**
     * 事务接受后需要往数据库写入的sql
     * @author Blues
     *
     */
    public function callBeforeApplicationDestroyedCallbacks()
    {
        parent::callBeforeApplicationDestroyedCallbacks();
        //事务在这里已经解除了
        try {
            foreach ($this->insertSql as $sql) {
                \DB::insert($sql);
            }
        } catch (\Exception $e) {

        }
//
    }

    function assertLog($content, $level = null)
    {
        if (app(LogAssert::class)->search($content, $level)) {
            $this->assertTrue(true);
        } else {
            throw new \Exception(" AssertLog Error: " . $content . ' not match');
        }
    }


}


