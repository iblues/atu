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

        $Caches = Cache::store('file');

        //缓存系统;
        $routes = $Caches->get('router');
        if (!$routes) {
            $routes = Routes::getRoutes();
            //路由缓存60秒
            //先不缓存. 后面做下处理. 检测路由对应文件的mtime改了才清理缓存.
//            $Caches->set('router', $routes,60);
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

        $cacheRoutes = $Caches->get('TodoFileList', []);

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
                if ($cache && $tmp = Arr::get($cacheRoutes, $cacheName, null)) {
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
                            if ($cache)
                                $cacheRoutes[$cacheName] = 'noAtu';
                            continue;
                        }

                        $methodAnnotations = $annotationReader->getMethodAnnotations($reflectionMethod);

                        $route['httpMethod'] = $route['method'];
                        $route['class'] = $class;
                        $route['classPath'] = $fileName;
                        $route['method'] = $method;
                        $route['methodStartLine'] = $reflectionMethod->getStartLine();//函数开始的行数
                        $route['annotation'] = $methodAnnotations;
                        if ($cache)
                            $cacheRoutes[$cacheName] = $route;
                        $return[] = $route;


                    } catch (\Exception $e) {
                        throw new \Exception('解析失败:' . $class . '@' . $method . "\n" . $e->getMessage());
                    }
                }
            }
        }

        $Caches->set('TodoFileList', $cacheRoutes, now()->addMinutes(60));

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