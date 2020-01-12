<h1 align="center"> annotation-test-unit </h1>

<p align="center"> Base on annotation's and laravel phpunit.
一个基于注解和laravel单元测试的 自动化测试包

</p>
<p>
配个视频在这里!~
</p>

## Installing

1.composer 安装
```shell
1.$ composer require iblues/annotation-test-unit -vvv
```
2.配置好单元测试( 相关教程请百度)

3.找一个控制器.增加注解.

```
    //请确认有匹配的路由, 程序是根据路由的映射表进行查找. 如果路由映射错误, 会无法执行. 后期会处理这种问题
    /**
     * @Test\Api(
     *     @Test\Now(),
     *     @Test\Request(),
     *     @Test\Response(200,{
     *      "code":true,
     *      "data":{{"id":true,"user":true}}
     *     }),
     * )
     */
    public function index(Request $request)
    ....


   /**
     * @Test\Api(
     *     path:1, 
     *     @Test\Now(),
     *     @Test\Request(),
     *     @Test\Response(200,{
     *      "code":true,
     *      "data":{{"id":true,"user":true}}
     *     }),
     * )
     */
    public function index($id,Request $request)
    ....
```
4.创建单元测试文件,运行即可.
```
namespace Tests\Feature;

use Iblues\AnnotationTestUnit\Traits\ApiTest;
use Tests\TestCase;

/**
 * 测试标记了@test|now的模块
 * @package Tests\Feature
 */
class AnnotationTest extends TestCase
{
    use ApiTest;
    /**
     * 测试标记了@test|now的模块
     */
    public function testNow(){
       $this->doNow();
    }
    /**
     * 测试所有@Test/Api模块
     */
    public function testAll(){
       $this->doAll();
    }

}
```

    


## Usage

### 文档说明
```
注意逗号!


@testNow //代表执行的测试要执行这个.避免全部执行很慢.

@testDebug //返回的内容都打印出来
//@testTransaction true //事务默认就是开启.可以不设置

@testApi (代表是api的测试)
@testApi ( url = http://baidu.com , method=GET)
@testApi ( url = /api/test/test/1 , method=POST)
@testApi ( path = 1) (会自动寻找匹配的路由 等于 /api/test/test/1)

@test\Request({1:21})   //json参数
//@test\Request({file:@storage(12.txt)} ) //代表文件路径的
@test\Request({2:1})//代表文件路径的

@testBefore(/test/test/TestBoot()); //优先调其他的
//@testBefore(@test('expressOrder')); //会将请求和返回的结果存下来 再议

@test\Response( 200 )  //默认就是200
@Test\Response({
  "data":true,
  "data":{
    {"id":true}
   },
   @Test\assertJson(...), //等等其他的 
}),
```
### DEMO

1.简易版 请求看是不是返回200
```
@Test\Api(
   @Test\Now(),
   @Test\Request(),
   @Test\Response({
      "data":true
   }),
)
```
2.验证返回结果版本
```
@Test\Api(
   @Test\Now(),
   @Test\Request(),
   @Test\Response({
      "data":true,
      "data":{
        {"id":true}
       }
   }),
)
```
3.复杂版本.同一个控制器 多种请求.多个返回结果
```

@Test\Api(
  @Test\Now(),
  @Test\Before(/test/test::function),
  @Test\Request(),
  @Test\Response(200,{
   "code":true,
   "data":{{"id":true,"user":true}},
    @Test\Now(),@Test\Debug()
  }),
  @Test\Debug()
)

```

4.结合larfree
```
@Test\Api //完了. 所有参数自动构建.
```



## 其他  函数的常规测试 未完成的 待续
```
@TestNow
@assert(1,2) == 3
```

## TodoList
- [ ] testApi
- [x] testNow
- [x] testParam
- [ ] testParam 文件上传支持
- [x] testResponse
- [ ] testResponse,正则和高级规则支持
- [ ] testBefore
- [x] testResponse
- [ ] testTransaction
- [ ] testDebug
- [ ] testName , testBefore

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/iblues/annotation-test-unit/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/iblues/annotation-test-unit/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT