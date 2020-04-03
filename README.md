<h1 align="center"> Annotation-test-unit (ATU) </h1>

<p align="center">Laravel ATU : A phpunit Tool Base on annotation and laravel. 一个基于注解和laravel的单元测试包. 

</p>

### 这个扩展包有啥用? 
<p>
1.改变你的开发方式. 改完代码,切换浏览器/postman 请求接口 烦不烦? 
    
2.顺带完成高覆盖测试.
    
后面配个视频在这里!~

qq交流群:814333044

兼容laravel5.5+/6/7
</p>

## Installing

1.composer 安装
```shell
$ composer require iblues/atu --dev
```
2.配置好单元测试,教程:
<https://www.w3cschool.cn/intellij_idea_doc/using_phpunit_framework.html>
PS:有效的设置测试环境 phpunit.xml 其中:
 
    a.SESSION_DRIVER会设置为array, 避免脏环境.
    b.QUEUE_DRIVER自动覆盖为sync, 方便测试和发现问题.
    c.CACHE_DRIVER会设置为array, 避免脏环境.
    d.也可以单独设置测试专用数据库

3.找一个控制器.增加注解.

```php
    //请确保use以下
    use Iblues\AnnotationTestUnit\Annotation as ATU;

    //请确认有匹配的路由, 程序是根据路由的映射表进行查找. 如果路由映射错误, 会无法执行. 后期会处理这种问题
    /**
     * @ATU\Api(
     *     @ATU\Now(),
     * )
     */
    public function index(Request $request){//...}

    /**
     * 请确保 xx/xx/1有数据
     * @ATU\Api(
     *     "path":1   
     *      @ATU\Now(),
     * )
     */
    public function show(Request $request){//...}

   /**
     * @ATU\Api(
     *     @ATU\Now(),
     *     @ATU\Request({"title":122}),
     *     @ATU\Response({
     *      "data":{"id":true,"title":122}
     *     })
     * )
     */
    public function store($id,Request $request){//...}
```
4.执行以下命令,会创建tests/api/AtuTest.php. 测试该文件即可. Tips: ctrl+r / 开启toggle auto test 即可重新运行测试,加快效率!
```shell script
php artisan vendor:publish --tag ATU
```
[See File](https://github.com/iblues/annotation-test-unit/blob/master/src/Publish/tests/Api/AtuTest.php).
    
## 如何更爽快的coding?
### 怎么爽快?
<p>
 1.有完整的代码提示.
 
 2.可以注解快速跳转.方便快速查看代码和文档
</p>

### 安装插件
1.安装phpstorm插件.

 https://plugins.jetbrains.com/plugin/index?xmlId=de.espend.idea.php.annotation
 
 2.设置插件
 language & framew -> php ->annotations ->Use Alias 新增
 Iblues\AnnotationTestUnit\Annotation  as  ATU
 
 
## Usage

[详细DEMO](https://github.com/iblues/atu/DEMO.md)

### 文档说明
```
注意事项:
受第三方扩展限制

1.必须逗号分隔, 否则报错 got '@' at position

2.类似以下数组[1,2,3]需要改写为{1,2,3}

3.字符串必须适应双引号. 如{"title":1} , 否则报错 got ''' 

@ATU\Api (代表是api的测试)
@ATU\Api( path = http://baidu.com , method=GET , title="测试" , author="xx")
@ATU\Api( path = /api/test/test/1 , method=POST)
@ATU\Api( path = 1) (会自动寻找匹配的路由 等于 /api/test/test/1)
@ATU\Api( path = [1,2,3]) (会依次匹配: /api/{x1}/{x2}/{x3} )

@ATU\Now //代表执行的测试要执行这个.避免全部执行很慢. 在Test\Api中

@ATU\Debug //返回的内容都打印出来

@ATU\Tag("tag1")  //用于标记分类.
@ATU\Tag(["tag1","tag2"]) 

@ATU\Request({1:21})   //json参数
//@ATU\Request({file:@storage(12.txt)} ) //代表文件路径的 未完成

@ATU\Before({test/test::class,"call"},{"param1"}); //调对应类的方法
@ATU\Before("call",{"param1"}); //调test类本身的方法. 可以在方法中调用setParam存储. 再@GetPrarm()调用
@ATU\Before(@ATU\Tag("user.admin")); //调用其他的tag进行关联性测试

@ATU\After("setParam",{"userAdmin",@ATU\GetResponse()}), //配合before+tag使用

@ATU\Login(false|100|0) // false的时候不登录,  100指定用户id为100的  0随意获取一个用户 

@ATU\Response( 413 )  //默认就是200
@ATU\Response( {"id":1} ) 
@ATU\Response( {"id":"/^测试.*?/i"} )  //支持正则表达式
@ATU\Response(200,{
  "data":true,
  "data":{
    {"id":true}
   },
   @ATU\Assert("assertJsonMissingExact",{"tt":1}), //等于response进行断言. 参考 https://learnku.com/docs/laravel/6.x/http-tests/5183#available-assertions
}),

//可以传入@Response和@request 会处理成对应值返回.
@ATU\Assert("assertDatabaseHas",{"user",
    { "title" : @ATU\Response('data.title") }
}),
@ATU\Assert("assertDatabaseHas",{"user",
    { "title" :{"id":1} }
}),

关于@ATU\Assert
在Api中支持超的函数
https://phpunit.readthedocs.io/zh_CN/latest/assertions.html#assertarrayhaskey

数据库相关.
assertDatabaseHas($table, array $data);    断言数据库表中包含给定的数据。
assertDatabaseMissing($table, array $data);    断言数据库中的表不包含给定数据。
assertSoftDeleted($table, array $data);    断言给定记录已被软删除。

也可以在类中自行增加自定义函数.
```

[详细DEMO](https://github.com/iblues/atu/DEMO.md)

## FAQ
Q: 报错 got '@' at position
A: 注解错误,  经常是少了逗号.


Q: 报错  got ''' 
A: 注解中请用双引号. 单引号不行. 如@ATU\Before("login");

Q: 报错 Illuminate\Contracts\Container\BindingResolutionException : Target class [env] does not exist.
A: telescope冲突 解决办法件 TELESCOPE.md
## TodoList
@ATU\
  v1.0版本
- [x] Api
- [x] Now
- [x] Request
- [x] Response,正则支持
- [x] getRequest
- [x] Response
- [x] getResponse
- [x] Before
- [x] Degbug
- [x] Assert
- [x] Response
- [x] GetParam
- [x] Tag
- [x] response 关于 GetRequest和GetParam
- [x] request 关于 getParam
- [x] before 关于 getParam
- [x] Assert 关于 getParam
- [x] Ignore
- [x] RouteIgnore 忽略路由检查,(第三方扩展包中:写了注释,但是不一定绑定路由的用)
- [x] title
- [x] 全局debug (在测试文件中启动)
- [x] Telescope初步集成
- [x] before 高级: 在before中调用其他tag.进行关联性测试
- [x] 增加关于日志的断言

  v1.1
- [ ] artisan的测试
- [ ] Request 文件上传,随机种子

  v1.2
- [ ] Template 测试模板的定义和调用

  v1.3
- [ ] event断言
- [ ] Telescope完美集成

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/iblues/atu/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/iblues/atu/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT