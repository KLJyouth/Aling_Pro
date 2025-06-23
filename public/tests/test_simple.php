<?php
echo "PHP服务器正常工作！当前时间：" . date('Y-m-d H:i:s');';
echo "<br>当前目录：" . __DIR__;";
echo "<br>文件列表：<br>";";
private $files = scandir(__DIR__);
foreach($files as $file) {
    if($file != '.' && $file != '..') {';
        echo "- " . $file . "<br>";";
    }
}
?>
