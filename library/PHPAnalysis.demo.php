<?php

// 严格开发模式
ini_set('display_errors', 'On');
ini_set('memory_limit', '64M');
error_reporting(E_ALL);

$t1 = $ntime = microtime(true);
$endtime = 'No action performed, no statistics!';
function print_memory($rc, &$infostr)
{
	global $ntime;
	$cutime = microtime(true);
	$etime = sprintf('%0.4f', $cutime - $ntime);
	$m = sprintf('%0.2f', memory_get_usage() / 1024 / 1024);
	$infostr .= "{$rc}: &nbsp;{$m} MB 用时：{$etime} 秒<br />\n";
	$ntime = $cutime;
}

header('Content-Type: text/html; charset=utf-8');

$memory_info = '';
print_memory('no action', $memory_info);

require_once 'phpanalysis.class.php';

$str = (isset($_POST['source']) ? $_POST['source'] : '');

$loadtime = $endtime1  = $endtime2 = $slen = 0;

$do_fork = $do_unit = true;
$do_multi = $do_prop = $pri_dict = false;

if ($str != '') {
	$do_fork = empty($_POST['do_fork']) ? false : true;
	$do_unit = empty($_POST['do_unit']) ? false : true;
	$do_multi = empty($_POST['do_multi']) ? false : true;
	$do_prop = empty($_POST['do_prop']) ? false : true;
	$pri_dict = empty($_POST['pri_dict']) ? false : true;

	$tall = microtime(true);

	PhpAnalysis::$loadInit = false;
	$pa = new PhpAnalysis('utf-8', 'utf-8', $pri_dict);
	print_memory('Initialize the object', $memory_info);

	$pa->LoadDict();
	print_memory('Load basic dictionary', $memory_info);

	$pa->SetSource($str);
	$pa->differMax = $do_multi;
	$pa->unitWord = $do_unit;

	$pa->StartAnalysis($do_fork);
	print_memory('perform participle', $memory_info);

	$okresult = $pa->GetFinallyResult(' ', $do_prop);
	print_memory('output word segmentation result', $memory_info);

	$pa_foundWordStr = $pa->foundWordStr;

	$t2 = microtime(true);
	$endtime = sprintf('%0.4f', $t2 - $t1);

	$slen = strlen($str);
	$slen = sprintf('%0.2f', $slen / 1024);

	$pa = '';
}

$teststr = "Indonesia is the best";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>word segmentation test</title>
</head>

<body>
	<table width='90%' align='center'>
		<tr>
			<td>

				<hr size='1' />

				<form id="form1" name="form1" method="post" action="?ac=done" style="margin:0px;padding:0px;line-height:24px;">
					<b>Source text:</b>&nbsp; <a href="PHPAnalysis.dict_build.php" target="_blank">[update dictionary]</a> <br />
					<textarea name="source" style="width:98%;height:150px;font-size:14px;"><?php echo (isset($_POST['source']) ? $_POST['source'] : $teststr); ?></textarea>
					<br />
					<input type='checkbox' name='do_fork' value='1' <?php echo ($do_fork ? "checked='1'" : ''); ?> />disambiguation
					<input type='checkbox' name='do_unit' value='1' <?php echo ($do_unit ? "checked='1'" : ''); ?> />new word recognition
					<input type='checkbox' name='do_multi' value='1' <?php echo ($do_multi ? "checked='1'" : ''); ?> />multivariate segmentation
					<input type='checkbox' name='do_prop' value='1' <?php echo ($do_prop ? "checked='1'" : ''); ?> />part-of-speech tagging
					<input type='checkbox' name='pri_dict' value='1' <?php echo ($pri_dict ? "checked='1'" : ''); ?> />Preload all entries
					<br />
					<input type="submit" name="Submit" value="Submit for segmentation" />
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="reset" name="Submit2" value="reset form data" />
				</form>
				<br />
				<textarea name="result" id="result" style="width:98%;height:120px;font-size:14px;color:#555"><?php echo (isset($okresult) ? $okresult : ''); ?></textarea>
				<br /><br />
				<b>Debug info:</b>
				<hr />
				<font color='blue'>String length:</font><?php echo $slen; ?>K <font color='blue'>Automatic word recognition:</font><?php echo (isset($pa_foundWordStr)) ? $pa_foundWordStr : ''; ?><br />
				<hr />
				<font color='blue'>Memory usage and execution time:</font>(Indicates the memory being occupied after completing an action)
				<hr />
				<?php echo $memory_info; ?>
				Total time:<?php echo $endtime; ?> 秒
			</td>
		</tr>
	</table>
</body>

</html>