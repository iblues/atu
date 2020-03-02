<?php

namespace Tests\Api;

use Iblues\AnnotationTestUnit\Traits\ApiTest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * 注解测试
 * @author Blues
 */
class AtuTest extends TestCase
{
    /**
     * DatabaseTransactions自动开启事务. 这样不会写入数据库. 但是注意!事务外的应用读不起到数据库的写入值.
     * PS: 如果开启事务后仍有数据写入.那么说明在代码中你使用了手动事务并且\DB::beginTransaion()比\DB::commit()少了一个.导致事务拦截失效
     */
    use ApiTest, DatabaseTransactions;

    /**
     * 是否深度隔离各个$app. 开启:降低性能,setUp会执行多次. 关闭:可能造成未知错误.
     */
    protected $isolateApp = true;

    /**
     * 是否启用缓存,加速解析.
     * 系统会根据文件最后修改时间自动刷新缓存, 但是也可能判断不完整. 可以根据实际情况判断
     */
    protected $cache = false;

    /**
     * 用户模型,用于登录使用.
     * @var string
     */
    protected $userModel = App\Models\User::class;

    /**
     * Auth guard
     * @var string
     */
    protected $guard = 'api';

    /**
     * 符合要求的api才执行.
     * @var array
     */
    public $whiteList = ['get' => [], 'put' => [], 'delete' => [], 'post' => []];
//    protected $whiteList = ['get' => ['/api\/admin\/.*?/i'], 'put'=>['/api\/[^((?!admin).)*$]/i', 'delete'=>'', 'post'=>''];

    /**
     * 满足要求的api不执行.
     * @var array
     */
    public $blackList = ['get' => [], 'put' => [], 'delete' => [], 'post' => []];
//    protected $blackList = ['get' => ['/api\/admin\/.*?/i'], 'put'=>['/api\/[^((?!admin).)*$]/i', 'delete'=>'', 'post'=>''];

    /**
     * 是否开启全局debug模式.相当于给每个都加了@DEBUG
     * @var bool
     */
    protected $debug = false;


    /**
     * 测试带有@ATU\Api()注解的
     * @author Blues
     */
    public function testAll()
    {
        $this->doAll();
//        $this->doAll(['tag'=>'tag1']);  Look for @ATU\Tag("product"), @ATU\Tag(["product","order"]);
    }

    /**
     * 测试带有@ATU\Api和@ATU\Now注解的
     * @author Blues
     *
     */
    public function testNow()
    {
        $this->doNow();
//        $this->doNow(['tag'=>'tag1']);  Look for @ATU\Tag("product");
    }

    /**
     * 读取所有带有@ATU\Api注解的,看是否有对应的路由匹配. 如果没有匹配路由就报错. 可用@ignore暂时忽略
     * @author Blues
     */
    public function testRouter()
    {
        $this->checkRouter();
    }

    /**
     * 用于给before使用的. 可以生成用户.
     * @author Blues
     */
    public function createUser()
    {
        $user = [
            'password' => $this->getPassword(),
            'email' => $this->getRandEmail(),
            'phone' => $this->getRandPhone(),
            'name' => '测试',
        ];
        $model = $this->userModel::firstOrCreate($user);
        $this->setParam('user', $model);
    }

}