<?php

namespace Iblues\AnnotationTestUnit\Libs;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;
use ReflectionMethod;

class Annotation
{

    /**
     * @param string $filter
     * @param bool $cache
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \ReflectionException
     * @author Blues
     *
     */
    static function getApiTest($filter = '', $cache = true)
    {
        $now = Arr::get($filter, 'now', 0);
        $name = Arr::get($filter, 'name', '');
        $tag = Arr::get($filter, 'tag', []);
        $while = Arr::get($filter, 'whiteList', []);
        $black = Arr::get($filter, 'blackList', []);

        $Cache = Cache::store('file');

        //缓存系统;
        $routes = $Cache->get('router');
        if (!$routes) {
            $routes = Routes::getRoutes();
            //路由缓存60秒
            //先不缓存. 后面做下处理. 检测路由对应文件的mtime改了才清理缓存.
//            $Cache->set('router', $routes,60);
        }

        $return = [];


        //初始化解析器
        $annotationReader = new AnnotationReader();
        //OpenApi部分不用去解析
        $nameSpaceWhitelist = [
            "OA", 'SWA', 'ORM'
        ];
        foreach ($nameSpaceWhitelist as $v) {
            AnnotationReader::addGlobalIgnoredNamespace($v);
        }
        AnnotationRegistry::registerLoader('class_exists');


        foreach ($routes as $key => $route) {

            if (strpos($route['path'], '@')) {
                list($class, $method) = explode('@', $route['path']);
                if (!method_exists($class, $method)) {
                    continue;
                }

                $list = $while[strtolower($route['method'])] ?? null;

                //如果不在白名单
                if ($list && !self::urlCheckList($list, $route['url'])) {
                    continue;
                }

                $list = $black[strtolower($route['method'])] ?? null;

                //如果在黑名单
                if ($list && self::urlCheckList($list, $route['url'])) {
                    continue;
                }


                $fileName = (new ReflectionClass($class))->getFileName();

                //获取最后修改时间
                $mtime = filemtime($fileName);
                $cacheName = md5(json_encode($route)) . $mtime;

                //如果修改时间没有变化,就读取缓存的.加快读取速度
                if ($cache && $tmp = $Cache->get($cacheName)) {
//                    $Cache->set($cacheName,null);
                    if ($tmp != 'noAtu') {
                        $return[] = $tmp;
                    }

                } else {

                    try {
                        $reflectionMethod = new ReflectionMethod($class, $method);
                        $doc = $reflectionMethod->getDocComment();

                        //存在注释@ATU\Api 才解析
                        if (!stripos($doc, '@ATU\Api') !== false) {
                            //缓存24小时
                            $Cache->set($cacheName, 'noAtu', now()->addMinutes(60 * 24));
                            continue;
                        }

//                        //忽略的
//                        if (stripos($doc, '@ATU\ignore') !== false) {
//                            //忽略的就不执行了
//                            continue;
//                        }


                        //有Test\Now的才执行
                        if ($now && !stripos($doc, '@ATU\Now')) {
                            //这个就不能缓存了.
                            continue;
                        }


                        $methodAnnotations = $annotationReader->getMethodAnnotations($reflectionMethod);

                        $route['httpMethod'] = $route['method'];
                        $route['class'] = $class;
                        $route['classPath'] = $fileName;
                        $route['method'] = $method;
                        $route['methodStartLine'] = $reflectionMethod->getStartLine();//函数开始的行数
                        $route['annotation'] = $methodAnnotations;
                        $Cache->set($cacheName, $route, 3600);
                        $return[] = $route;


                    } catch (\Exception $e) {
                        throw new \Exception('解析失败:' . $class . '@' . $method . "\n" . $e->getMessage());
                    }
                }
            }
        }
        return $return;
    }

    static function urlCheckList($list, $url)
    {
        foreach ($list as $p) {
            return preg_match($p, $url);
        }
        return true;
    }


    static function dirToArray($dir)
    {
        $result = array();
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $result[$value] = self::dirToArray($dir . DIRECTORY_SEPARATOR . $value);
                } else {
                    $result[] = $value;
                }
            }
        }
        return $result;
    }
}