<?php

if (!\function_exists('config')) {
    /**
     * @param string $configName
     * @param string $path
     * @return mixed
     * @throws Exception
     */
    function config(string $configName, string $path = '')
    {
        $config = CONFIG_PATH . '/' . $path . '/' . $configName . '.php';

        if (!file_exists($config)) {
            throw new \Exception("File {$configName}.php does not exists");
        }

        return include $config;
    }
}

if (!function_exists('storage_path')) {
    /**
     * @param string $fileName
     * @return string
     */
    function storage_path (string $fileName): string
    {
        return STORAGE_FILE_PATH . '/' . $fileName;
    }
}
