
### DEMO

1.简易版 请求看是不是返回200
```
@ATU\Api(
   @ATU\Now(),
   @ATU\Request(),
   @ATU\Response({
      "data":true
   }),
)
```
2.验证返回结果版本 , 并输出debug
```
@ATU\Api(
   @ATU\Now(),
   @ATU\Debug(),
   @ATU\Request(),
   @ATU\Response({
      "data":true,
      "data":{
        {"id":true}
       }
   }),
)
```
3.复杂版本.同一个控制器 多种请求.多个返回结果;assert调用其他断言(可以调用自定义函数)
```

@ATU\Api(
  title="something",
  @ATU\Now(),
  @ATU\Before("createUser"),
  @ATU\Request(),
  @ATU\Response(200,{
   "code":true,
   "data":{{"id":true,"user":true}},
  }),
  @ATU\Debug()
)

@ATU\Api(
  @ATU\Now(),
  @ATU\Request({"title":122}),
  @ATU\Response({
   "data":{"title":122}
  }),
  @ATU\Debug()
),


@ATU\Api(
  @ATU\Now(),
  @ATU\Request({"title":1}),
  @ATU\Response(422,{
   "data":{"title":true}
  }),
  @ATU\Debug()
)

复杂版本. 囊括了大部分用法

@ATU\Api(
  path = 1,
  method = "PUT",
  @ATU\Now(),
  @ATU\Request({"title":"测试","content":123}),
  @ATU\Response({
     "data":{"id":true,"title":"测试"}
    },
    @ATU\Assert("assertSee",{"测试"}),
    @ATU\Assert("assertSee",{@ATU\GetRequest("title")}),
    @ATU\Assert("assertJson", {{"data":@ATU\GetRequest}} ),
    @ATU\Assert("assertOk"),
  ),
  @ATU\Assert("assertDatabaseHas",{"test_test",{"id":1}} ),
  @ATU\Assert("assertDatabaseHas",{"test_test",@ATU\GetRequest()}),
  @ATU\Assert("assertDatabaseHas",{"test_test",
   { "title" : @ATU\GetResponse("data.title") }
  }),
),


@ATU\Api(
  @ATU\Before("create",{TestTest::class,{"title":"测试22"} }),
  @ATU\Request({"title":@ATU\GetParam("TestTest.title"),"content":@ATU\GetParam("TestTest.title"),"user_id":"123"}),
  @ATU\Response({
     "data":{"id":true,"title":@ATU\GetRequest("title")}
    },
    @ATU\Assert("assertSee",{@ATU\GetParam("TestTest.title")}),
    @ATU\Assert("assertSee",{@ATU\GetRequest("title")}),
    @ATU\Assert("assertJson", {{"data":@ATU\GetRequest}} ),
    @ATU\Assert("assertOk"),
  ),
  @ATU\Assert("assertDatabaseHas",{"test_test",{"id":1}} ),
  @ATU\Assert("assertDatabaseHas",{"test_test",@ATU\GetRequest()} ),
  @ATU\Assert("assertDatabaseHas",{"test_test",
   { "title" : @ATU\GetResponse("data.title") }
  }),
),


@ATU\Api(
  @ATU\Now(),
  path={"http://baidu.com/",@ATU\GetParam("test.id"),"/otherUrl"},
  method="GET",
  @ATU\Before("createTest"),
  @ATU\Response({
   "status":1,
   "data":{"title":@ATU\GetParam("test.title")},
  }),
  @ATU\Assert("assertDatabaseHas",{"test_test",{"title":@ATU\GetParam("test.title")} }),
)
```

4.暂时忽略
```
@ATU\Api(
  @ATU\Ignore
)
```

5.调用模板 未完成
```
@ATU\Api(
    @ATU\Larfree('tes.test')
)

//然后定义? 
```

6.DEMO文件

[DEMO示例](https://github.com/iblues/larfree-permission/blob/master/src/Controllers/User/AdminController.php)
