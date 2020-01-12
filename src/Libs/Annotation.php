<?php

namespace Iblues\AnnotationTestUnit\Libs;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use ReflectionMethod;

class Annotation
{
    /**
     * @param string $now
     * @param string $name
     * @param array $tag
     * @author Blues
     *
     */
    static function getApiTest($now = '', $name = '', $tag = [])
    {
        $routes = Routes::getRoutes();
        $return = [];
        foreach ($routes as $key => $route) {
            if (strpos($route['path'], '@')) {
                list($class, $method) = explode('@', $route['path']);
                if (!method_exists($class, $method)) {
                    continue;
                }
                $annotationReader = new AnnotationReader();

                //OpenApi部分不用去解析
                $whitelist = [
                    "OA", 'SWA', 'ORM'
                ];
                foreach ($whitelist as $v) {
                    AnnotationReader::addGlobalIgnoredNamespace($v);
                }

                AnnotationRegistry::registerLoader('class_exists');
                try {
                    $reflectionMethod = new ReflectionMethod($class, $method);
                    $doc = $reflectionMethod->getDocComment();

                    //存在注释@Test\Api 才解析
                    if (!stripos($doc, '@Test\Api') !== false) {
                        continue;
                    }

                    //有Test\Now的才执行
                    if ($now && !stripos($doc, '@Test\Now')) {
                        continue;
                    }


                    $methodAnnotations = $annotationReader->getMethodAnnotations($reflectionMethod);

                    $route['annotation'] = $methodAnnotations;
                    $return[] = $route;


                } catch (\Exception $e) {
                    throw new \Exception('解析失败:' . $class . '@' . $method . ':' . $e->getMessage());
                }
            }
        }
        return $return;
    }


    static function getPhpDoc()
    {

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