<h1 align="center"> annotation-test-unit </h1>

<p align="center"> Base on annotation's and laravel phpunit.
一个基于注解和laravel单元测试的 自动化测试包

</p>
<p>
配个视频在这里!~
</p>

## Installing

```shell
$ composer require iblues/annotation-test-unit -vvv
```

## Usage

### 文档说明
```
@testName myTest //可以不要. 如果要了.可以结合before

@testNow //代表执行的测试要执行这个.避免全部执行很慢.

@testDebug //返回的内容都打印出来

@testTransaction true //事务默认就是开启.可以不设置

@testApi (代表是api的测试)
@testApi post:http://baidu.com
@testApi put:/api/test/test/1
@testApi delete:1 (会自动寻找匹配的路由 等于 /api/test/test/1)

@testRequest {1:21}   //json参数
@testRequest {file:@storage(12.txt)} //代表文件路径的
@testRequest {2:1} //代表文件路径的

@testBefore /test/test/TestBoot(); //优先调其他的
@testBefore @test('expressOrder'); //会将请求和返回的结果存下来

@testResponse 200  //默认就是200
@testResponse data.id=true,data.title="测试" //id字段必须有. title必须=123
@AssertSeeText 其他高级函数
```
### DEMO

1.简易版 请求看是不是返回200
```
@testApi  //必须要有一个 会自动寻找匹配的路由 
@testRequest {test:21}
```
2.验证返回结果版本
```
@testApi put:1
@testRequest {id:21}
@testResponse data.id=$request.id,data.title=/^1/ //id字段必须有. 满足正则
```
3.复杂版本.同一个控制器 多种请求.多个返回结果
```
@testApi put:/api/test/test/1
@testBefore /test/test/TestBoot(1); //优先调其他的
@testRequest {id:21}
@testResponse data.id=$request.id,data.title=/^1/ //id字段必须有. 满足正则

@testApi put:/api/test/test/1 //必须多写一个 代表一个新的测试
@testBefore /test/test/TestBoot(1); //优先调其他的
@testBefore setLogin(1); //优先调其他的
@testRequest {id:21}
@testResponse 401
@testResponse data.error.id=true
@assertSeeText ...
```

4.结合larfree
```
@testApi //完了. 所有参数自动构建.
```



## 其他  函数的常规测试 待续
```
@testNow
@assert(1,2) == 3
```

## TodoList
- [ ] testApi
- [ ] testNow
- [ ] testParam
- [ ] testResponse
- [ ] testBefore
- [ ] testResponse
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