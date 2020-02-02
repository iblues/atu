<?php

namespace Iblues\AnnotationTestUnit\Libs;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Arr;

class Routes
{
    /**
     * @return \Illuminate\Routing\RouteCollection
     * @author Blues
     */
    static function getRoutes()
    {
        $routes = Route::getRoutes();
        return $routes = self::parseRoutes($routes);
    }


    static function parseRoutes($apiRoutes)
    {
        $data = [];
        foreach ($apiRoutes as $route) {
            $url = $route->uri;
            $method = $route->methods[0];
            $as = @$route->action['controller'];
            $data[] = ['path' => $as, 'method' => $method, 'url' => $url];
        }
        return $data;
    }

    static function checkAllRoute($testClass, $path = null)
    {
        //根据路由读取出来的
        $todoList = Annotation::getApiTest([], false);

        //扫码文件夹.
        if (!$path)
            $path = app_path('/');

        $fileList = self::dirToArray($path, true);
        $fileList = Arr::flatten($fileList);
        foreach ($fileList as $file) {
            if (substr($file, '-4') == '.php') {
                $class = self::getClassFromFile($file, '@ATU\Api');
                if ($class) {
                    try {
                        self::checkClassHasRoute($class, $todoList);
                        //如果没有报错,说明通过了
                        $testClass->assertTrue(true);
                    } catch (\Exception $e) {
                        throw $e;
                    }
                }
            }
        }
    }

    static function checkClassHasRoute($classNameSpace, $routes)
    {
        $class = new \ReflectionClass($classNameSpace);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        $routes = Arr::pluck($routes, 'path', 'path');
        foreach ($methods as $method) {
            $doc = $method->getDocComment();
            if ($doc && stripos($doc, '@ATU\Api') !== false) {
                $methodName = $method->getName();
                $fullPath = $classNameSpace . '@' . $methodName;
                $filePath = $class->getFileName();
                if (stripos($doc, '@ATU\Ignore') !== false) {
                    echo '@ATU\Ignore in ' . $fullPath . ' ( ' . $filePath . ':' . $method->getStartLine() . ' ) ';
                    continue;
                }
                if (!in_array($fullPath, $routes)) {
                    throw new \Exception($fullPath . " hasn't match route. You can use @ATU\Ignore to ignore that ATU \r\n " . $filePath . ':' . $method->getStartLine());
                }
            }
        }
    }

    /**
     * 根据路径获取类的命名空间
     * @param $path_to_file
     * @return mixed|string
     * @author Blues
     *
     */
    static function getClassFromFile($path_to_file, $tag = false)
    {
        //Grab the contents of the file
        $contents = file_get_contents($path_to_file);

        if ($tag && stripos($contents, $tag) === false) {
            return;
        }
        //Start with a blank namespace and class
        $namespace = $class = "";
        //Set helper values to know that we have found the namespace/class token and need to collect the string values after them
        $getting_namespace = $getting_class = false;
        //Go through each token and evaluate it as necessary
        foreach (token_get_all($contents) as $token) {
            //If this token is the namespace declaring, then flag that the next tokens will be the namespace name
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $getting_namespace = true;
            }
            //If this token is the class declaring, then flag that the next tokens will be the class name
            if (is_array($token) && $token[0] == T_CLASS) {
                $getting_class = true;
            }
            //While we're grabbing the namespace name...
            if ($getting_namespace === true) {
                //If the token is a string or the namespace separator...
                if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {
                    //Append the token's value to the name of the namespace
                    $namespace .= $token[1];
                } else if ($token === ';') {
                    //If the token is the semicolon, then we're done with the namespace declaration
                    $getting_namespace = false;
                }
            }
            //While we're grabbing the class name...
            if ($getting_class === true) {
                //If the token is a string, it's the name of the class
                if (is_array($token) && $token[0] == T_STRING) {
                    //Store the token's value as the class name
                    $class = $token[1];
                    //Got what we need, stope here
                    break;
                }
            }
        }
        //Build the fully-qualified class name and return it
        return $namespace ? $namespace . '\\' . $class : $class;
    }

    static function dirToArray($dir, $fullPath = false)
    {
        $result = array();
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $result[$value] = self::dirToArray($dir . DIRECTORY_SEPARATOR . $value, $fullPath);
                } else {
                    if ($fullPath) {
                        $dir = str_ireplace('//', '/', $dir . '/');
                        $result[] = str_ireplace('//', '/', str_ireplace('//', '/', $dir . $value));
                    } else {
                        $result[] = $value;
                    }

                }
            }
        }
        return $result;
    }
}