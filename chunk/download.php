<?php
$filePath = '../doc/big.pdf';

chunk_download_file($filePath);

/**
 * 分篇下载的汉书
 *
 * @param $file
 * @param $fname
 */
function chunk_download_file($file, $fname = 'chunk.pdf')
{
    $fhandle = fopen($file, 'rb');//文件句柄
    $fsize = filesize($file);//文件大小

    //断点续传和整个文件下载的判断，支持多段下载
    if (!empty($_SERVER['HTTP_RANGE'])) {
        $range = str_replace("=", "-", $_SERVER['HTTP_RANGE']);
        $match = explode("-", $range);
        $start = $match[1];
        $end = !empty($match[2]) ? $match[2] : $fsize - 1;
    } else {
        $start = 0;
        $end = $fsize - 1;
    }

    if (($end - $start) < ($fsize - 1)) {
        fseek($fhandle, $start);
        header("HTTP/1.1 206 Partial Content");
        header("Content-Length: " . ($end - $start + 1));
        header("Content-Range: bytes " . $start . "-" . $end . "/" . $fsize);
    } else {
        header("HTTP/1.1 200 OK");
        header("Content-Length: $fsize");
        Header("Accept-Ranges: bytes");
        header("Content-Range: bytes " . $start . "-" . $end . "/" . $fsize);
    }

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment;filename=$fname");

    if (!feof($fhandle)) {
        set_time_limit(0);
        $buffer = fread($fhandle, $end - $start + 1);
        echo $buffer;
        flush();
        ob_flush();
    }
}