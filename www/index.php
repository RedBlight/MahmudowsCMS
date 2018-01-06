<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Mahmudows</title>
<link rel="shortcut icon" href="favicon.ico">
<link rel="stylesheet" type="text/css" href="MCMSR/css/index.css" />
<meta name="keywords" content="mahmut, akkuş, mahmudows, makaleler, programlama, oyunlar, netherlord" />
<meta http-equiv="Content-Language" content="tr-TR" />
<meta name="author" content="Mahmut Akkuş" />
</head>

<body>

<?php 
$menu = ($_GET[menu] == null) ? 1 : $_GET[menu];
$cate = ($_GET[cate] == null) ? 1 : $_GET[cate];
$page = ($_GET[page] == null) ? 1 : $_GET[page];
$menuCount = intval(file_get_contents("MCMSR/site/menucount.txt"));
$cateCount = intval(file_get_contents("MCMSR/site/".$menu."/catecount.txt"));
$pageCount = intval(file_get_contents("MCMSR/site/".$menu."/".$cate."/pagecount.txt"));
$menuLabel = file_get_contents("MCMSR/site/".$menu."/label.txt");
$cateLabel = file_get_contents("MCMSR/site/".$menu."/".$cate."/label.txt");
$pageLabel = file_get_contents("MCMSR/site/".$menu."/".$cate."/".$page."/label.txt");
?>

<img src="MCMSR/img/logo.png" width="800" height="105"/><br>
<img src="MCMSR/img/siluet.png" width="828" height="59"/><br>

<p class="topMenu">
<?
for($i=1; $i<=$menuCount; $i++)
{
	if($i==$menu) { echo '<span class="topButon"><span class="topSeciliButon"><a href="index.php?menu='.$i.'">'.file_get_contents("MCMSR/site/".$i."/label.txt").'</a></span></span>'; }
	else { echo '<span class="topButon"><a href="index.php?menu='.$i.'">'.file_get_contents("MCMSR/site/".$i."/label.txt").'</a></span>'; }
}
?>
</p>

<table align="center" width="960" border="0" cellspacing="0" cellpadding="0">
<tr>

<td class="yanMenu" width="180">
<?php
for($i=1; $i<=$cateCount; $i++)
{
	echo '<p class="solHeaderButon">'.file_get_contents("MCMSR/site/".$menu."/".$i."/label.txt").'</p>';
	$pageCounti = intval(file_get_contents("MCMSR/site/".$menu."/".$i."/pagecount.txt"));
	for($ii=1; $ii<=$pageCounti; $ii++)
	{
		if($ii==$page && $i==$cate) { echo '<p class="solSeciliButon"><a href="index.php?menu='.$menu.'&cate='.$i.'&page='.$ii.'">&gt; '.file_get_contents("MCMSR/site/".$menu."/".$i."/".$ii."/label.txt").'</a></p>'; }
		else { echo '<p class="solButon"><a href="index.php?menu='.$menu.'&cate='.$i.'&page='.$ii.'">&#8226; '.file_get_contents("MCMSR/site/".$menu."/".$i."/".$ii."/label.txt").'</a></p>'; }
	}
}
?>
</td>

<td class="icerik" width="600">
<?php
echo '<div class="baslik">'.$cateLabel.'</div>';
echo '<div class="subBaslik">-- '.$pageLabel.' --</div><br/>';
include("MCMSR/site/".$menu."/".$cate."/".$page."/content.txt");
?>
<br><br><p class="altYazi"><a href="http://www.mahmudows.com">&#169; 2012 - Mahmut Akkuş</a></p>
</td>

<td class="yanMenu" width="180">
\********************/<br>
\*****************/<br>
\**************/<br>
\***********/<br>
\********/<br>
REKLAM<br>ALANI<br>
/********\<br>
/***********\<br>
/**************\<br>
/*****************\<br>
/********************\<br>
</td>
    
</tr>
</table>
</body>
</html>