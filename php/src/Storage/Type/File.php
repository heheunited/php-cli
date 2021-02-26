<?php
declare(strict_types=1);

namespace App\Storage\Type;

class File
{
    public const JSON_EXTEND = '.json';
    public const TXT_EXTEND = '.txt';

    /**
     * @param string $fileName
     * @param string $data
     * @param string $extend
     * @return string
     */
    public function save(string $fileName, string $data, string $extend = self::TXT_EXTEND): string
    {
        $fileName .= $extend;

        $fopen = fopen($fileName, 'ab+');
        fwrite($fopen, $data);
        fclose($fopen);

        return $fileName;
    }
}
