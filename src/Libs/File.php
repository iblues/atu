<?php


namespace Iblues\AnnotationTestUnit\Libs;


class File
{
    const DIRNAME = 'ATU';

    /**
     * remove h ago files
     * @param int $hour
     * @author Blues
     */
    static public function clearFile($hour = 1)
    {

        self::mkdir();
        $filePath = storage_path(self::DIRNAME);
        $list = scandir($filePath);
        foreach ($list as $file) {
            $name = pathinfo($file);
            if ($name['filename'] && $name['filename'] != '.') {
                $time = explode('-', $name['filename']);
                if (isset($time[1]) && $time[1] < (time() - 3600 * $hour) . '000') {
                    unlink($filePath . '/' . $file);
                }
            }
        }
    }

    static function mkdir()
    {
        $filePath = storage_path(self::DIRNAME);
        if (!file_exists($filePath)) {
            mkdir($filePath);
        }
        //创建git忽略
        if (!file_exists($filePath . '/.gitignore')) {
            file_put_contents($filePath . '/.gitignore', "*\r\n!.gitignore");
        }
    }

    static function saveFile($file, $data, $toJson = false)
    {
        self::mkdir();
        $filePath = storage_path(self::DIRNAME);
        if ($toJson)
            $ext = '.json';
        else
            $ext = '.txt';
        $file = $filePath . '/' . $file . '-' . self::msectime() . $ext;
        if ($toJson) {
            $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        file_put_contents($file, $data);
        return $file;
    }

    /**
     * 返回毫秒
     * @return float
     * @author Blues
     *
     */
    static function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }
}