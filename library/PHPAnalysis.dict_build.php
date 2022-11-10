<?php
ini_set('memory_limit', '128M');
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');
require_once('PHPAnalysis.class.php');
$dicAddon = __DIR__.'/dict/not-build/base_dic_full.txt';

if( empty($_GET['ac']) )
{
    echo "<div style='line-height:28px;'>Please select an action to take:<br />";
    echo "1、<a href='?ac=make'>with original file(dict/not-build/base_dic_full.txt)generate a standard dictionary;</a><br />";
    echo "2、<a href='?ac=revert'>from default dictionary(dict/base_dic_full.dic), decompile the original file.</a></div>";
    exit();
}

if( $_GET['ac']=='make' )
{
    PhpAnalysis::$loadInit = false;
    $pa = new PhpAnalysis('utf-8', 'utf-8', false);
    $pa->MakeDict( $dicAddon );
    echo "Dictionary creation complete!";
    exit();
}
else
{
    $pa = new PhpAnalysis('utf-8', 'utf-8', true);
    $pa->ExportDict('base_dic_source.txt');
    echo "After decompiling the dictionary file, the generated file is：base_dic_source.txt ！";
    exit();
}
