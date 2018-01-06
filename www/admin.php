<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Mahmudows CMS</title>
<link rel="stylesheet" type="text/css" href="MCMSR/css/admin.css" />
</head>
<body>
<div class="logo">
MAHMUDOWS CMS<br/>
-- Admin Paneli --
</div>

<?php
//Starting timer
function getTime() 
{
 $a = explode (' ', microtime());
 return(double) $a[0] + $a[1];
}
$start = getTime();
 
// Defining "Mahmudows CMS Root"
$MCMSR = "httpdocs/MCMSR"; // Buraya MCMSR adlı klasörün sitenizde bulunacağı yolu yazacaksınız

// Creating FTP stream for password system
$ftpStream = ftp_connect("ftp.benimsitem.com"); // Buraya FTP sunucunuzu yazacaksınız
ftp_login($ftpStream, "FTP-admin", "FTP-pass"); // Buraya da FTP sunucunuzun kullanıcı bilgilerini gireceksiniz
ftp_raw($ftpStream, "SITE CHMOD 0777 ".$MCMSR."/secured");

// Reading password from file
$passwordInFile = file_get_contents("MCMSR/secured/pass.txt");
$keyInFile = file_get_contents("MCMSR/secured/key.txt");

// Defining functions
//General Functions
function HTML_NavBar($keyNew)
{
	$navbarHTML="<table width=\"100%\" class=\"panel\" border=\"1\" cellspacing=\"0\" cellpadding=\"1\"><tr><td";
	if($_GET[cmspage]==1) { $navbarHTML .= " class=\"panelS\"><a href=\"admin.php?cmspage=1&key=".$keyNew."\">Genel Durum</a></td><td"; }
	else { $navbarHTML .= "><a href=\"admin.php?cmspage=1&key=".$keyNew."\">Genel Durum</a></td><td"; }
	if($_GET[cmspage]==2) { $navbarHTML .= " class=\"panelS\"><a href=\"admin.php?cmspage=2&key=".$keyNew."\">Tasarımı Düzenle</a></td><td"; }
	else { $navbarHTML .= "><a href=\"admin.php?cmspage=2&key=".$keyNew."\">Tasarımı Düzenle</a></td><td"; }
	if($_GET[cmspage]>2 && $_GET[cmspage]<18) { $navbarHTML .= " class=\"panelS\"><a href=\"admin.php?cmspage=3&key=".$keyNew."\">Sayfaları Düzenle</a></td><td"; }
	else { $navbarHTML .= "><a href=\"admin.php?cmspage=3&key=".$keyNew."\">Sayfaları Düzenle</a></td><td"; }
	if($_GET[cmspage]==100) { $navbarHTML.=" class=\"panelS\"><a href=\"admin.php?cmspage=100&key=".$keyNew."\">Admin Şifresini Değiştir</a></td><td"; }
	else { $navbarHTML .= "><a href=\"admin.php?cmspage=100&key=".$keyNew."\">Admin Şifresini Değiştir</a></td><td"; }
	if($_GET[cmspage]==101) { $navbarHTML.=" class=\"panelS\"><a href=\"admin.php?cmspage=101&key=".$keyNew."\">Güvenli Çıkış</a></td>"; }
	else { $navbarHTML .= "><a href=\"admin.php?cmspage=101&key=".$keyNew."\">Güvenli Çıkış</a></td>"; }
	$navbarHTML.="</tr>
	</table>";
	return $navbarHTML;
}
function HTML_AskPass()
{
	return "<div class=\"icerik\"><br/>
	Mahmudows CMS Admin Paneli'ne hoş geldiniz.<br/>
	Panele erişmek için admin şifresini girmelisiniz:<br/>
	<form action=\"admin.php?cmspage=1\" method=\"post\" name=\"askpass\">
	<input name=\"passwordInput\" type=\"password\" value=\"\" size=\"40\" maxlength=\"40\"/><br/>
	<input type=\"submit\" value=\"Devam Et\" name=\"b1\">
	</form>
	</div><br/>";
}
function HTML_AskPassAgain()
{
	return "<div class=\"icerik\"><br/>
	Mahmudows CMS Admin Paneli'ne hoş geldiniz.<br/>
	Panele erişmek için admin şifresini girmelisiniz:<br/>
	<form action=\"admin.php?cmspage=1\" method=\"post\" name=\"askpass\">
	<input name=\"passwordInput\" type=\"password\" value=\"\" size=\"40\" maxlength=\"40\"/><br/>
	<span style=\"color:red\">* Girdiğiniz şifre yanlış, lütfen tekrar deneyin... *</span><br/>
	<input type=\"submit\" value=\"Devam Et\" name=\"b1\">
	</form>
	</div><br/>";
}
function GenerateNewKey() 
{
    $charString = "0123456789abcdefghijklmnopqrstuvwxyz";
	$length = 10;
    $keyNew = "";    
    for($i=0; $i<$length; $i++) { $keyNew .= $charString[mt_rand(0, strlen($charString))]; }
	unlink("MCMSR/secured/key.txt");
	$keyFile = fopen("MCMSR/secured/key.txt", 'w');
	fwrite($keyFile, $keyNew); 
	fclose($keyFile); 
	return $keyNew;
}
function DeleteDirectoryFTP($ftpStream, $deletePath)
{
	if(!is_resource($ftpStream) || get_resource_type($ftpStream) !== 'FTP Buffer') { return false; }
	$i = 0;
	$files = array();
	$folders = array();
	$statusNext = false;
	$currentFolder = $deletePath;
	$list = ftp_rawlist($ftpStream, $deletePath, true);
	foreach($list as $current)
	{
		if(empty($current))
		{
			$statusNext = true;
			continue;
		}
		if($statusNext === true)
		{
			$currentFolder = substr($current, 0, -1);
			$statusNext = false;
			continue;
		}
		$split = preg_split('[ ]', $current, 9, PREG_SPLIT_NO_EMPTY);
		$entry = $split[8];
		$isDir = ($split[0]{0} === 'd') ? true : false;
		if($entry==='.' || $entry==='..') { continue; }
		if($isDir === true) { $folders[] = $currentFolder.'/'.$entry; }
		else { $files[] = $currentFolder.'/'.$entry; }
	}
	foreach($files as $file) { ftp_delete($ftpStream, $file); }
	rsort($folders);
	foreach($folders as $folder) { ftp_rmdir($ftpStream, $folder); }
	return ftp_rmdir($ftpStream, $deletePath);
}

//Menu Edit Functions
function MenuAction_Delete($ftpStream, $MCMSR, $menuCount, $menuLabel)
{
	if($_POST[deleteApproval]=="EVET")
	{
		ftp_raw($ftpStream, "CWD /");
		$deletePath = $MCMSR."/site/".$_GET[menu];
		DeleteDirectoryFTP($ftpStream, $deletePath);
		if($_GET[menu]<$menuCount)
		{
			$movingMenu = $_GET[menu]+1;
			ftp_raw($ftpStream,"CWD ".$MCMSR."/site");
			for($i=$menuCount-$_GET[menu]; $i>0; $i--)
			{
				ftp_raw($ftpStream,"RNFR ".strval($movingMenu)); 
				ftp_raw($ftpStream,"RNTO ".strval($movingMenu-1));
				$movingMenu++;	
			}
		}
		unlink("MCMSR/site/menucount.txt");
		$file_menuCount = fopen("MCMSR/site/menucount.txt", 'w');
		fwrite($file_menuCount, strval($menuCount-1));
		fclose($file_menuCount);
		ftp_raw($ftpStream,"CWD /");
		$cmspageReport = "<span style=\"color:green\"><b>\"".$menuLabel."\"</b> adlı menü başarılı bir şekilde silinmiştir...</span>";
	}
	else { $cmspageReport = "<span style=\"color:red\"><b>\"".$menuLabel."\"</b> adlı menü, onay metni olarak EVET yazılmadığından dolayı silinememiştir...</span>"; }
	return $cmspageReport;
}
function MenuAction_MoveUp($ftpStream, $MCMSR, $menuLabel)
{
	if($_GET[menu]>1)
	{
		ftp_raw($ftpStream, "CWD ".$MCMSR."/site");
		ftp_raw($ftpStream, "RNFR ".$_GET[menu]); 
		ftp_raw($ftpStream, "RNTO 0");
		ftp_raw($ftpStream, "RNFR ".strval($_GET[menu]-1));
		ftp_raw($ftpStream, "RNTO ".$_GET[menu]);
		ftp_raw($ftpStream, "RNFR 0");
		ftp_raw($ftpStream, "RNTO ".strval($_GET[menu]-1));
		ftp_raw($ftpStream, "CWD /");
		$cmspageReport = "<span style=\"color:green\"><b>\"".$menuLabel."\"</b> menüsü bir sıra yukarı alındı...</span>";
	}
	else { $cmspageReport = "<span style=\"color:red\"><b>\"".$menuLabel."\"</b> menüsü zaten en yukarıda...</span>"; }
	return $cmspageReport;
}
function MenuAction_MoveDown($ftpStream, $MCMSR, $menuCount, $menuLabel)
{
	if($_GET[menu]<$menuCount)
	{
		ftp_raw($ftpStream, "CWD ".$MCMSR."/site");
		ftp_raw($ftpStream, "RNFR ".$_GET[menu]); 
		ftp_raw($ftpStream, "RNTO 0");
		ftp_raw($ftpStream, "RNFR ".strval($_GET[menu]+1));
		ftp_raw($ftpStream, "RNTO ".$_GET[menu]);
		ftp_raw($ftpStream, "RNFR 0");
		ftp_raw($ftpStream, "RNTO ".strval($_GET[menu]+1));
		ftp_raw($ftpStream, "CWD /");
		$cmspageReport = "<span style=\"color:green\"><b>\"".$menuLabel."\"</b> menüsü bir sıra aşağı alındı...</span>";
	}
	else { $cmspageReport = "<span style=\"color:red\"><b>\"".$menuLabel."\"</b> menüsü zaten en aşağıda...</span>"; }
	return $cmspageReport;
}
function MenuAction_Add($ftpStream, $MCMSR, $menuCount)
{
	ftp_raw($ftpStream,"CWD ".$MCMSR."/site");
	ftp_raw($ftpStream,"MKD ".strval($menuCount+1));
	ftp_raw($ftpStream,"SITE CHMOD 0777 ".strval($menuCount+1));
	$file_label = fopen("MCMSR/site/".strval($menuCount+1)."/label.txt", 'w');
	fwrite($file_label, $_POST[newMenuName]);
	fclose($file_label);
	unlink("MCMSR/site/menucount.txt");
	$file_menuCount = fopen("MCMSR/site/menucount.txt", 'w');
	fwrite($file_menuCount, strval($menuCount+1));
	fclose($file_menuCount);
	$menuCount = intval(file_get_contents("MCMSR/site/menucount.txt"));
	ftp_raw($ftpStream,"CWD /");
	$cmspageReport = "<span style=\"color:green\"><b>\"".$_POST[newMenuName]."\"</b> menüsü başarılı bir şekilde oluşturulmuştur...</span>";
	return $cmspageReport;
}
function MenuAction_Rename($menuLabel)
{
	$labelPath = "MCMSR/site/".$_GET[menu]."/label.txt";
	unlink($labelPath);
	$file_label = fopen($labelPath, 'w');
	fwrite($file_label, $_POST[changedMenuName]);
	fclose($file_label);
	$cmspageReport = "<span style=\"color:green\"><b>\"".$menuLabel."\"</b> adlı menünün adı <b>\"".$_POST[changedMenuName]."\"</b> olarak değiştirilmiştir...</span>";
	return $cmspageReport;
}
function HTML_MenuEditPage($keyNew)
{
	$menuCount = intval(file_get_contents("MCMSR/site/menucount.txt"));
	$menuEditPageHTML = "<div class=\"icerik\"><center><br/>
	Aşağıdaki tabloda site içerisindeki menüleri görmektesiniz:<br/>
	<br/>
	<table class=\"tablo\" width=\"960px\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\">
	<tr>
	<th width=\"460px\">Menü Adı</th>
	<th width=\"100px\">Düzenle</th>
	<th width=\"100px\">Sil</th>
	<th width=\"100px\">Ad Değiş</th>
	<th width=\"100px\">Yukarı Al</th>
	<th width=\"100px\">Aşağı al</th>
	</tr>";
	for($i=1; $i<=$menuCount; $i++)
	{
		$menuEditPageHTML .= "<tr>
		<td>".file_get_contents("MCMSR/site/".$i."/label.txt")."</td>
		<td><a href=\"admin.php?cmspage=4&menu=".$i."&key=".$keyNew."\" title=\"Düzenle\">X<a/></td>
		<td><a href=\"admin.php?cmspage=3&menu=".$i."&ask=1&key=".$keyNew."\" title=\"Sil\">X<a/></td>
		<td><a href=\"admin.php?cmspage=3&menu=".$i."&ask=5&key=".$keyNew."\" title=\"Ad Değiş\">X<a/></td>
		<td><a href=\"admin.php?cmspage=3&menu=".$i."&action=2&key=".$keyNew."\" title=\"Yukarı Al\">X<a/></td>
		<td><a href=\"admin.php?cmspage=3&menu=".$i."&action=3&key=".$keyNew."\" title=\"Aşağı Al\">X<a/></td>
		</tr>";
	}
	$menuEditPageHTML .= "<tr>
	<td>&nbsp;</td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	</tr>
	<tr>
	<td><a href=\"admin.php?cmspage=3&ask=4&key=".$keyNew."\" title=\"Yeni Menü Ekle\"><i>+ Yeni Menü Ekle</i><a/></td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	</tr>
	</table>";
	return $menuEditPageHTML;
}
function HTML_MenuAsk_Delete($menuLabel, $keyNew)
{
	$menuAskHTML = "<br/>
	<b>\"".$menuLabel."\"</b> adlı menüyü tüm içeriğiyle beraber silmek istediğinize emin misiniz?<br/>
	Bu işlemin geri dönüşü olmayacaktır!<br/>
	Devam etmek için aşağıdaki boşluğa EVET yazıp \"Devam Et\" butonuna tıklayın.
	<form action=\"admin.php?cmspage=3&menu=".$_GET[menu]."&action=1&key=".$keyNew."\" method=\"post\" name=\"deleteApproval\">
	<input name=\"deleteApproval\" type=\"text\" value=\"\" size=\"4\" maxlength=\"4\"/><br/>
	<input type=\"submit\" value=\"Devam Et\" name=\"b1\">
	</form><br/>
	<a href=\"admin.php?cmspage=3&key=".$keyNew."\" title=\"Silmekten Vazgeç\"><u>&lt;Silmekten Vazgeç&gt;</u><a/>";
	return $menuAskHTML;
}
function HTML_MenuAsk_Add($keyNew)
{
	$menuAskHTML = "<br/>
	Eklemek istediğiniz yeni menünün adını girin:<br/>
	<form action=\"admin.php?cmspage=3&action=4&key=".$keyNew."\" method=\"post\" name=\"newmenu\">
	<input name=\"newMenuName\" type=\"text\" value=\"Yeni Menü\" size=\"40\" maxlength=\"40\"/><br/>
	<input type=\"submit\" value=\"Ekle\" name=\"b1\">
	</form><br/>
	<a href=\"admin.php?cmspage=3&key=".$keyNew."\" title=\"Eklemekten Vazgeç\"><u>&lt;Eklemekten Vazgeç&gt;</u><a/>";
	return $menuAskHTML;
}
function HTML_MenuAsk_Rename($menuLabel, $keyNew)
{
	$menuAskHTML = "<br/>
	<b>\"".$menuLabel."\"</b> adlı menünün yeni adını girin:<br/>
	<form action=\"admin.php?cmspage=3&menu=".strval($_GET[menu])."&action=5&key=".$keyNew."\" method=\"post\" name=\"renamenu\">
	<input name=\"changedMenuName\" type=\"text\" value=\"".$menuLabel."\" size=\"40\" maxlength=\"40\"/><br/>
	<input type=\"submit\" value=\"Değiştir\" name=\"b1\">
	</form><br/>
	<a href=\"admin.php?cmspage=3&key=".$keyNew."\" title=\"Ad Değişikliğinden Vazgeç\"><u>&lt;Ad Değişikliğinden Vazgeç&gt;</u><a/>";
	return $menuAskHTML;
}

//Category Edit functions
function CateAction_Delete($ftpStream, $MCMSR, $cateCount, $cateLabel)
{
	if($_POST[deleteApproval]=="EVET")
	{
		ftp_raw($ftpStream, "CWD /");
		$deletePath = $MCMSR."/site/".$_GET[menu]."/".$_GET[cate];
		DeleteDirectoryFTP($ftpStream, $deletePath);
		if($_GET[cate]<$cateCount)
		{
			$movingCate = $_GET[cate]+1;
			ftp_raw($ftpStream,"CWD ".$MCMSR."/site/".$_GET[menu]);
			for($i=$cateCount-$_GET[cate]; $i>0; $i--)
			{
				ftp_raw($ftpStream,"RNFR ".strval($movingCate)); 
				ftp_raw($ftpStream,"RNTO ".strval($movingCate-1));
				$movingCate++;	
			}
		}
		unlink("MCMSR/site/".$_GET[menu]."/catecount.txt");
		$file_cateCount = fopen("MCMSR/site/".$_GET[menu]."/catecount.txt", 'w');
		fwrite($file_cateCount, strval($cateCount-1));
		fclose($file_cateCount);
		ftp_raw($ftpStream,"CWD /");
		$cmspageReport = "<span style=\"color:green\"><b>\"".$cateLabel."\"</b> adlı kategori başarılı bir şekilde silinmiştir...</span>";
	}
	else { $cmspageReport = "<span style=\"color:red\"><b>\"".$cateLabel."\"</b> adlı kategori, onay metni olarak EVET yazılmadığından dolayı silinememiştir...</span>"; }
	return $cmspageReport;
}
function CateAction_MoveUp($ftpStream, $MCMSR, $cateLabel)
{
	if($_GET[cate]>1)
	{
		ftp_raw($ftpStream,"CWD ".$MCMSR."/site/".$_GET[menu]);
		ftp_raw($ftpStream, "RNFR ".$_GET[cate]); 
		ftp_raw($ftpStream, "RNTO 0");
		ftp_raw($ftpStream, "RNFR ".strval($_GET[cate]-1));
		ftp_raw($ftpStream, "RNTO ".$_GET[cate]);
		ftp_raw($ftpStream, "RNFR 0");
		ftp_raw($ftpStream, "RNTO ".strval($_GET[cate]-1));
		ftp_raw($ftpStream, "CWD /");
		$cmspageReport = "<span style=\"color:green\"><b>\"".$cateLabel."\"</b> kategorisi bir sıra yukarı alındı...</span>";
	}
	else { $cmspageReport = "<span style=\"color:red\"><b>\"".$cateLabel."\"</b> kategorisi zaten en yukarıda...</span>"; }
	return $cmspageReport;
}
function CateAction_MoveDown($ftpStream, $MCMSR, $cateCount, $cateLabel)
{
	if($_GET[cate]<$cateCount)
	{
		ftp_raw($ftpStream,"CWD ".$MCMSR."/site/".$_GET[menu]);
		ftp_raw($ftpStream, "RNFR ".$_GET[cate]); 
		ftp_raw($ftpStream, "RNTO 0");
		ftp_raw($ftpStream, "RNFR ".strval($_GET[cate]+1));
		ftp_raw($ftpStream, "RNTO ".$_GET[cate]);
		ftp_raw($ftpStream, "RNFR 0");
		ftp_raw($ftpStream, "RNTO ".strval($_GET[cate]+1));
		ftp_raw($ftpStream, "CWD /");
		$cmspageReport = "<span style=\"color:green\"><b>\"".$cateLabel."\"</b> kategorisi bir sıra aşağı alındı...</span>";
	}
	else { $cmspageReport = "<span style=\"color:red\"><b>\"".$cateLabel."\"</b> kategorisi zaten en aşağıda...</span>"; }
	return $cmspageReport;
}
function CateAction_Add($ftpStream, $MCMSR, $cateCount)
{
	ftp_raw($ftpStream,"CWD ".$MCMSR."/site/".$_GET[menu]);
	ftp_raw($ftpStream,"MKD ".strval($cateCount+1));
	ftp_raw($ftpStream,"SITE CHMOD 0777 ".strval($cateCount+1));
	$file_label = fopen("MCMSR/site/".$_GET[menu]."/".strval($cateCount+1)."/label.txt", 'w');
	fwrite($file_label, $_POST[newCateName]);
	fclose($file_label);
	unlink("MCMSR/site/".$_GET[menu]."/catecount.txt");
	$file_cateCount = fopen("MCMSR/site/".$_GET[menu]."/catecount.txt", 'w');
	fwrite($file_cateCount, strval($cateCount+1));
	$cateCount = intval(file_get_contents("MCMSR/site/".$_GET[menu]."/catecount.txt"));
	fclose($file_cateCount);
	ftp_raw($ftpStream,"CWD /");
	$cmspageReport = "<span style=\"color:green\"><b>\"".$_POST[newCateName]."\"</b> kategorisi başarılı bir şekilde oluşturulmuştur...</span>";
	return $cmspageReport;
}
function CateAction_Rename($cateLabel)
{
	$labelPath = "MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/label.txt";
	unlink($labelPath);
	$file_label = fopen($labelPath, 'w');
	fwrite($file_label, $_POST[changedCateName]);
	fclose($file_label);
	$cmspageReport = "<span style=\"color:green\"><b>\"".$cateLabel."\"</b> adlı kategorinin adı <b>\"".$_POST[changedCateName]."\"</b> olarak değiştirilmiştir...</span>";
	return $cmspageReport;
}
function HTML_CateEditPage($keyNew, $menuLabel)
{
	$cateCount = intval(file_get_contents("MCMSR/site/".$_GET[menu]."/catecount.txt"));
	$cateEditPageHTML = "<div class=\"icerik\"><center><br/>
	Aşağıdaki tabloda <b>\"".$menuLabel."\"</b> adlı menü içerisindeki kategorileri görüntülemektesiniz:<br/>
	<br/>
	<table class=\"tablo\" width=\"960px\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\">
	<tr>
	<th width=\"460px\">Kategori Adı</th>
	<th width=\"100px\">Düzenle</th>
	<th width=\"100px\">Sil</th>
	<th width=\"100px\">Ad Değiş</th>
	<th width=\"100px\">Yukarı Al</th>
	<th width=\"100px\">Aşağı al</th>
	</tr>";
	for($i=1; $i<=$cateCount; $i++)
	{
		$cateEditPageHTML .= "<tr>
		<td>".file_get_contents("MCMSR/site/".$_GET[menu]."/".$i."/label.txt")."</td>
		<td><a href=\"admin.php?cmspage=5&menu=".$_GET[menu]."&cate=".$i."&key=".$keyNew."\" title=\"Düzenle\">X<a/></td>
		<td><a href=\"admin.php?cmspage=4&menu=".$_GET[menu]."&cate=".$i."&ask=1&key=".$keyNew."\" title=\"Sil\">X<a/></td>
		<td><a href=\"admin.php?cmspage=4&menu=".$_GET[menu]."&cate=".$i."&ask=5&key=".$keyNew."\" title=\"Ad Değiş\">X<a/></td>
		<td><a href=\"admin.php?cmspage=4&menu=".$_GET[menu]."&cate=".$i."&action=2&key=".$keyNew."\" title=\"Yukarı Al\">X<a/></td>
		<td><a href=\"admin.php?cmspage=4&menu=".$_GET[menu]."&cate=".$i."&action=3&key=".$keyNew."\" title=\"Aşağı Al\">X<a/></td>
		</tr>";
	}
	$cateEditPageHTML .= "<tr>
	<td>&nbsp;</td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	</tr>
	<tr>
	<td><a href=\"admin.php?cmspage=4&menu=".$_GET[menu]."&ask=4&key=".$keyNew."\" title=\"Yeni Kategori Ekle\"><i>+ Yeni Kategori Ekle</i><a/></td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	</tr>
	<tr>
	<td>&nbsp;</td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	</tr>
	<tr>
	<td><a href=\"admin.php?cmspage=3&key=".$keyNew."\" title=\"Menü Sayfasına Dön\"><i>&lt;&lt; Menü Sayfasına Dön</i><a/></td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	</tr>
	</table>";
	return $cateEditPageHTML;
}
function HTML_CateAsk_Delete($cateLabel, $keyNew)
{
	$cateAskHTML = "<br/>
	<b>\"".$cateLabel."\"</b> adlı kategoriyi tüm içeriğiyle beraber silmek istediğinize emin misiniz?<br/>
	Bu işlemin geri dönüşü olmayacaktır!<br/>
	Devam etmek için aşağıdaki boşluğa EVET yazıp \"Devam Et\" butonuna tıklayın.
	<form action=\"admin.php?cmspage=4&menu=".$_GET[menu]."&cate=".$_GET[cate]."&action=1&key=".$keyNew."\" method=\"post\" name=\"deleteApproval\">
	<input name=\"deleteApproval\" type=\"text\" value=\"\" size=\"4\" maxlength=\"4\"/><br/>
	<input type=\"submit\" value=\"Devam Et\" name=\"b1\">
	</form><br/>
	<a href=\"admin.php?cmspage=4&menu=".$_GET[menu]."&key=".$keyNew."\" title=\"Silmekten Vazgeç\"><u>&lt;Silmekten Vazgeç&gt;</u><a/>";
	return $cateAskHTML;
}
function HTML_CateAsk_Add($keyNew)
{
	$cateAskHTML = "<br/>
	Eklemek istediğiniz yeni kategorinin adını girin:<br/>
	<form action=\"admin.php?cmspage=4&menu=".$_GET[menu]."&action=4&key=".$keyNew."\" method=\"post\" name=\"newcate\">
	<input name=\"newCateName\" type=\"text\" value=\"Yeni Kategori\" size=\"40\" maxlength=\"40\"/><br/>
	<input type=\"submit\" value=\"Ekle\" name=\"b1\">
	</form><br/>
	<a href=\"admin.php?cmspage=4&menu=".$_GET[menu]."&key=".$keyNew."\" title=\"Eklemekten Vazgeç\"><u>&lt;Eklemekten Vazgeç&gt;</u><a/>";
	return $cateAskHTML;
}
function HTML_CateAsk_Rename($cateLabel, $keyNew)
{
	$cateAskHTML = "<br/>
	<b>\"".$cateLabel."\"</b> adlı kategorinin yeni adını girin:<br/>
	<form action=\"admin.php?cmspage=4&menu=".$_GET[menu]."&cate=".$_GET[cate]."&action=5&key=".$keyNew."\" method=\"post\" name=\"renacate\">
	<input name=\"changedCateName\" type=\"text\" value=\"".$cateLabel."\" size=\"40\" maxlength=\"40\"/><br/>
	<input type=\"submit\" value=\"Değiştir\" name=\"b1\">
	</form><br/>
	<a href=\"admin.php?cmspage=4&menu=".$_GET[menu]."&key=".$keyNew."\" title=\"Ad Değişikliğinden Vazgeç\"><u>&lt;Ad Değişikliğinden Vazgeç&gt;</u><a/>";
	return $cateAskHTML;
}

//Page Edit Functions
function PageAction_Delete($ftpStream, $MCMSR, $pageCount, $pageLabel)
{
	if($_POST[deleteApproval]=="EVET")
	{
		ftp_raw($ftpStream, "CWD /");
		$deletePath = $MCMSR."/site/".$_GET[menu]."/".$_GET[cate]."/".$_GET[page];
		DeleteDirectoryFTP($ftpStream, $deletePath);
			
		if($_GET[page]<$pageCount)
		{
			$movingPage = $_GET[page]+1;
			ftp_raw($ftpStream, "CWD ".$MCMSR."/site/".$_GET[menu]."/".$_GET[cate]);
			for($i=$pageCount-$_GET[page]; $i>0; $i--)
			{
				ftp_raw($ftpStream,"RNFR ".strval($movingPage)); 
				ftp_raw($ftpStream,"RNTO ".strval($movingPage-1));
				$movingPage++;	
			}
		}
		
		unlink("MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/pagecount.txt");
		$file_pageCount = fopen("MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/pagecount.txt", 'w');
		fwrite($file_pageCount, strval($pageCount-1));
		fclose($file_pageCount);
		
		ftp_raw($ftpStream, "CWD /");
		$cmspageReport = "<span style=\"color:green\"><b>\"".$pageLabel."\"</b> adlı sayfa başarılı bir şekilde silinmiştir...</span>";
	}
	else { $cmspageReport = "<span style=\"color:red\"><b>\"".$pageLabel."\"</b> adlı sayfa, onay metni olarak EVET yazılmadığından dolayı silinememiştir...</span>"; }
	return $cmspageReport;
}
function PageAction_MoveUp($ftpStream, $MCMSR, $pageLabel)
{
	if($_GET[page]>1)
	{
		ftp_raw($ftpStream, "CWD ".$MCMSR."/site/".$_GET[menu]."/".$_GET[cate]);
		ftp_raw($ftpStream, "RNFR ".$_GET[page]); 
		ftp_raw($ftpStream, "RNTO 0");
		ftp_raw($ftpStream, "RNFR ".strval($_GET[page]-1));
		ftp_raw($ftpStream, "RNTO ".$_GET[page]);
		ftp_raw($ftpStream, "RNFR 0");
		ftp_raw($ftpStream, "RNTO ".strval($_GET[page]-1));
		ftp_raw($ftpStream, "CWD /");
		$cmspageReport = "<span style=\"color:green\"><b>\"".$pageLabel."\"</b> sayfası bir sıra yukarı alındı...</span>";
	}
	else { $cmspageReport = "<span style=\"color:red\"><b>\"".$pageLabel."\"</b> sayfası zaten en yukarıda...</span>"; }
	return $cmspageReport;
}
function PageAction_MoveDown($ftpStream, $MCMSR, $pageCount, $pageLabel)
{
	if($_GET[page]<$pageCount)
	{
		ftp_raw($ftpStream, "CWD ".$MCMSR."/site/".$_GET[menu]."/".$_GET[cate]);
		ftp_raw($ftpStream, "RNFR ".$_GET[page]); 
		ftp_raw($ftpStream, "RNTO 0");
		ftp_raw($ftpStream, "RNFR ".strval($_GET[page]+1));
		ftp_raw($ftpStream, "RNTO ".$_GET[page]);
		ftp_raw($ftpStream, "RNFR 0");
		ftp_raw($ftpStream, "RNTO ".strval($_GET[page]+1));
		ftp_raw($ftpStream, "CWD /");
		$cmspageReport = "<span style=\"color:green\"><b>\"".$pageLabel."\"</b> sayfası bir sıra aşağı alındı...</span>";
	}
	else { $cmspageReport = "<span style=\"color:red\"><b>\"".$pageLabel."\"</b> sayfası zaten en aşağıda...</span>"; }
	return $cmspageReport;
}
function PageAction_Add($ftpStream, $MCMSR, $pageCount)
{
	ftp_raw($ftpStream,"CWD ".$MCMSR."/site/".$_GET[menu]."/".$_GET[cate]);
	ftp_raw($ftpStream,"MKD ".strval($pageCount+1));
	ftp_raw($ftpStream,"SITE CHMOD 0777 ".strval($pageCount+1));
	$file_label = fopen("MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/".strval($pageCount+1)."/label.txt", 'w');
	fwrite($file_label, $_POST[newPageName]);
	fclose($file_label);
	unlink("MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/pagecount.txt");
	$file_pageCount = fopen("MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/pagecount.txt", 'w');
	fwrite($file_pageCount, strval($pageCount+1));
	fclose($file_pageCount);
	ftp_raw($ftpStream,"CWD /");
	$cmspageReport = "<span style=\"color:green\"><b>\"".$_POST[newPageName]."\"</b> sayfası başarılı bir şekilde oluşturulmuştur...</span>";
	return $cmspageReport;
}
function PageAction_Rename($pageLabel)
{
	$labelPath = "MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/".$_GET[page]."/label.txt";
	unlink($labelPath);
	$file_label = fopen($labelPath, 'w');
	fwrite($file_label, $_POST[changedPageName]);
	fclose($file_label);
	$cmspageReport = "<span style=\"color:green\"><b>\"".$pageLabel."\"</b> adlı sayfanın adı <b>\"".$_POST[changedPageName]."\"</b> olarak değiştirilmiştir...</span>";
	return $cmspageReport;
}
function HTML_PageEditPage($keyNew, $menuLabel, $cateLabel)
{
	$pageCount = intval(file_get_contents("MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/pagecount.txt"));
	$pageEditPageHTML = "<div class=\"icerik\"><center><br/>
	Aşağıdaki tabloda <b>\"".$menuLabel."\"</b> adlı menü içerisindeki <b>\"".$cateLabel."\"</b> adlı kategorinin sayfalarını görüntülemektesiniz:<br/>
	<br/>
	<table class=\"tablo\" width=\"960px\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\">
	<tr>
	<th width=\"460px\">Sayfa Adı</th>
	<th width=\"100px\">Düzenle</th>
	<th width=\"100px\">Sil</th>
	<th width=\"100px\">Ad Değiş</th>
	<th width=\"100px\">Yukarı Al</th>
	<th width=\"100px\">Aşağı al</th>
	</tr>";
	for($i=1; $i<=$pageCount; $i++)
	{
		$pageEditPageHTML .= "<tr>
		<td>".file_get_contents("MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/".$i."/label.txt")."</td>
		<td><a href=\"admin.php?cmspage=6&menu=".$_GET[menu]."&cate=".$_GET[cate]."&page=".$i."&key=".$keyNew."\" title=\"Düzenle\">X<a/></td>
		<td><a href=\"admin.php?cmspage=5&menu=".$_GET[menu]."&cate=".$_GET[cate]."&page=".$i."&ask=1&key=".$keyNew."\" title=\"Sil\">X<a/></td>
		<td><a href=\"admin.php?cmspage=5&menu=".$_GET[menu]."&cate=".$_GET[cate]."&page=".$i."&ask=5&key=".$keyNew."\" title=\"Ad Değiş\">X<a/></td>
		<td><a href=\"admin.php?cmspage=5&menu=".$_GET[menu]."&cate=".$_GET[cate]."&page=".$i."&action=2&key=".$keyNew."\" title=\"Yukarı Al\">X<a/></td>
		<td><a href=\"admin.php?cmspage=5&menu=".$_GET[menu]."&cate=".$_GET[cate]."&page=".$i."&action=3&key=".$keyNew."\" title=\"Aşağı Al\">X<a/></td>
		</tr>";
	}
	$pageEditPageHTML .= "<tr>
	<td>&nbsp;</td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	</tr>
	<tr>
	<td><a href=\"admin.php?cmspage=5&menu=".$_GET[menu]."&cate=".$_GET[cate]."&ask=4&key=".$keyNew."\" title=\"Yeni Sayfa Ekle\"><i>+ Yeni Sayfa Ekle</i><a/></td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	</tr>
	<tr>
	<td>&nbsp;</td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	</tr>
	<tr>
	<td><a href=\"admin.php?cmspage=4&menu=".$_GET[menu]."&key=".$keyNew."\" title=\"Kategori Sayfasına Dön\"><i>&lt;&lt; Kategori Sayfasına Dön</i><a/></td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	</tr>
	</table>";
	return $pageEditPageHTML;
}
function HTML_PageAsk_Delete($pageLabel, $keyNew)
{
	$pageAskHTML = "<br/>
	<b>\"".$pageLabel."\"</b> adlı sayfayı tüm içeriğiyle beraber silmek istediğinize emin misiniz?<br/>
	Bu işlemin geri dönüşü olmayacaktır!<br/>
	Devam etmek için aşağıdaki boşluğa EVET yazıp \"Devam Et\" butonuna tıklayın.
	<form action=\"admin.php?cmspage=5&menu=".$_GET[menu]."&cate=".$_GET[cate]."&page=".$_GET[page]."&action=1&key=".$keyNew."\" method=\"post\" name=\"deleteApproval\">
	<input name=\"deleteApproval\" type=\"text\" value=\"\" size=\"4\" maxlength=\"4\"/><br/>
	<input type=\"submit\" value=\"Devam Et\" name=\"b1\">
	</form><br/>
	<a href=\"admin.php?cmspage=5&menu=".$_GET[menu]."&cate=".$_GET[cate]."&key=".$keyNew."\" title=\"Silmekten Vazgeç\"><u>&lt;Silmekten Vazgeç&gt;</u><a/>";
	return $pageAskHTML;
}
function HTML_PageAsk_Add($keyNew)
{
	$pageAskHTML = "<br/>
	Eklemek istediğiniz yeni sayfanın adını girin:<br/>
	<form action=\"admin.php?cmspage=5&menu=".$_GET[menu]."&cate=".$_GET[cate]."&action=4&key=".$keyNew."\" method=\"post\" name=\"newpage\">
	<input name=\"newPageName\" type=\"text\" value=\"Yeni Sayfa\" size=\"40\" maxlength=\"40\"/><br/>
	<input type=\"submit\" value=\"Ekle\" name=\"b1\">
	</form><br/>
	<a href=\"admin.php?cmspage=5&menu=".$_GET[menu]."&cate=".$_GET[cate]."&key=".$keyNew."\" title=\"Eklemekten Vazgeç\"><u>&lt;Eklemekten Vazgeç&gt;</u><a/>";
	return $pageAskHTML;
}
function HTML_PageAsk_Rename($pageLabel, $keyNew)
{
	$pageAskHTML = "<br/>
	<b>\"".$pageLabel."\"</b> adlı sayfanın yeni adını girin:<br/>
	<form action=\"admin.php?cmspage=5&menu=".$_GET[menu]."&cate=".$_GET[cate]."&page=".$_GET[page]."&action=5&key=".$keyNew."\" method=\"post\" name=\"renapage\">
	<input name=\"changedPageName\" type=\"text\" value=\"".$pageLabel."\" size=\"40\" maxlength=\"40\"/><br/>
	<input type=\"submit\" value=\"Değiştir\" name=\"b1\">
	</form><br/>
	<a href=\"admin.php?cmspage=5&menu=".$_GET[menu]."&cate=".$_GET[cate]."&key=".$keyNew."\" title=\"Ad Değişikliğinden Vazgeç\"><u>&lt;Ad Değişikliğinden Vazgeç&gt;</u><a/>";
	return $pageAskHTML;
}

//Content Edit Functions
function EncodeContent($text) 
{
	$text = str_replace("<textarea>", "(textarea)", $text);
	$text = str_replace("</textarea>", "(/textarea)", $text);
	$text = str_replace("</form>", "(/form)", $text);
	$text = str_replace("<form>", "(form)", $text);
	return $text;
}
function DecodeContent($text) 
{
	$text = str_replace("(textarea)", "<textarea>", $text);
	$text = str_replace("(/textarea)", "</textarea>", $text);
	$text = str_replace("(/form)", "</form>", $text);
	$text = str_replace("(form)", "<form>", $text);
	return $text;
}
function ContentAction_Save()
{
	$contentPath = "MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/".$_GET[page]."/content.txt";
	unlink($contentPath);
	$file_content = fopen($contentPath, 'w');
	fwrite($file_content, DecodeContent($_POST[pageContentNew]));
	fclose($file_content);
	$cmspageReport = "<span style=\"color:green\"><b>Sayfa kodları başarılı bir şekilde kaydedilmiştir...</span>";
	return $cmspageReport;
}
function HTML_ContentEditPage($menuLabel, $cateLabel, $pageLabel, $keyNew)
{
	$contentHTML = file_get_contents("MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/".$_GET[page]."/content.txt");
	$contentEditPageHTML = '<center><div class="icerik"><br/>
	<b>"'.$menuLabel.'"</b> menüsündeki <b>"'.$cateLabel.'"</b> kategorisinin <b>"'.$pageLabel.'"</b> sayfasının html kodlarını görüntülemektesiniz:</div>
	<form name="contentform" method="post" action="admin.php?cmspage=6&menu='.$_GET[menu].'&cate='.$_GET[cate].'&page='.$_GET[page].'&action=1&key='.$keyNew.'">
	<textarea rows="20" name="pageContentNew" cols="100">'.EncodeContent($contentHTML).'</textarea><br/>
    <input name="b1" value="Kaydet" type="submit" />
	<a href="admin.php?cmspage=6&menu='.$_GET[menu].'&cate='.$_GET[cate].'&page='.$_GET[page].'&key='.$keyNew.'" target="_self"><input name="b2" type="button" value="Değişiklikleri Sıfırla" /></a>
	</form></center>';
	return $contentEditPageHTML;
}

// Password check
switch($_POST[passwordInput])
{
	case $passwordInFile: $keyInFile = GenerateNewKey(); $key = $keyInFile; break;
	case null: if($_GET[key] != null) { $key = base64_decode($_GET[key]); }	break;
	default: $key = "Access denied!!!";	break;
}

// Key check
switch($key)
{
	case null: echo HTML_AskPass(); break;
	default: echo HTML_AskPassAgain(); break;
	case $keyInFile: $keyNew = base64_encode(GenerateNewKey()); echo HTML_NavBar($keyNew);
///////////////////////////////////////////////////////////////////
////////////////////////////////CMS////////////////////////////////
///////////////////////////////////////////////////////////////////
switch($_GET[cmspage])
{
	// Site Status
	case 1:
	echo("
<div class=\"icerik\"><br/>
Bu kısımda siteyi tüm zamanalrda ve bu ay içerisinde kaç kişi ziyaret etmiş gibi bilgiler yer alacaktır.
</div><br/>
	"); 
	break;

	// Style Edit
	case 2:
	echo("
<div class=\"icerik\"><br/>
Bu kısımda sitenin CSS kodları görsel bir şekilde editlenebilir olacaktır.
</div><br/>
	");
	break;

	// Menu List Edit
	case 3:
	$menuCount = intval(file_get_contents("MCMSR/site/menucount.txt"));
	$menuLabel = file_get_contents("MCMSR/site/".$_GET[menu]."/label.txt");
	switch($_GET[action])
	{
		case 1: $cmspageReport = MenuAction_Delete($ftpStream, $MCMSR, $menuCount, $menuLabel); break;
		case 2: $cmspageReport = MenuAction_MoveUp($ftpStream, $MCMSR, $menuLabel); break;
		case 3: $cmspageReport = MenuAction_MoveDown($ftpStream, $MCMSR, $menuCount, $menuLabel);	break;
		case 4: $cmspageReport = MenuAction_Add($ftpStream, $MCMSR, $menuCount); break;
		case 5: $cmspageReport = MenuAction_Rename($menuLabel); break;
	}
	echo HTML_MenuEditPage($keyNew);
	switch($_GET[ask])
	{
		case 1: echo HTML_MenuAsk_Delete($menuLabel, $keyNew); break;
		case 4: echo HTML_MenuAsk_Add($keyNew); break;
		case 5: echo HTML_MenuAsk_Rename($menuLabel, $keyNew); break;
	}
	echo("<br/>".$cmspageReport."<br/></div></center>");
	break;
	
	// Category List Edit
	case 4:
	$cateCount = intval(file_get_contents("MCMSR/site/".$_GET[menu]."/catecount.txt"));
	$menuLabel = file_get_contents("MCMSR/site/".$_GET[menu]."/label.txt");
	$cateLabel = file_get_contents("MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/label.txt");
	
	switch($_GET[action])
	{
		case 1: $cmspageReport = CateAction_Delete($ftpStream, $MCMSR, $cateCount, $cateLabel); break;
		case 2: $cmspageReport = CateAction_MoveUp($ftpStream, $MCMSR, $cateLabel); break;
		case 3: $cmspageReport = CateAction_MoveDown($ftpStream, $MCMSR, $cateCount, $cateLabel);	break;
		case 4: $cmspageReport = CateAction_Add($ftpStream, $MCMSR, $cateCount); break;
		case 5: $cmspageReport = CateAction_Rename($cateLabel); break;
	}
	echo HTML_CateEditPage($keyNew, $menuLabel);
	switch($_GET[ask])
	{
		case 1: echo HTML_CateAsk_Delete($cateLabel, $keyNew); break;
		case 4: echo HTML_CateAsk_Add($keyNew); break;
		case 5: echo HTML_CateAsk_Rename($cateLabel, $keyNew); break;
	}
	echo("<br/>".$cmspageReport."<br/></div></center>");
	break;
	
	// Page List Edit
	case 5:
	$pageCount = intval(file_get_contents("MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/pagecount.txt"));
	$menuLabel = file_get_contents("MCMSR/site/".$_GET[menu]."/label.txt");
	$cateLabel = file_get_contents("MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/label.txt");
	$pageLabel = file_get_contents("MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/".$_GET[page]."/label.txt");
	
	switch($_GET[action])
	{
		case 1: $cmspageReport = PageAction_Delete($ftpStream, $MCMSR, $pageCount, $pageLabel); break;
		case 2: $cmspageReport = PageAction_MoveUp($ftpStream, $MCMSR, $pageLabel); break;
		case 3: $cmspageReport = PageAction_MoveDown($ftpStream, $MCMSR, $pageCount, $pageLabel);	break;
		case 4: $cmspageReport = PageAction_Add($ftpStream, $MCMSR, $pageCount); break;
		case 5: $cmspageReport = PageAction_Rename($pageLabel); break;
	}
	echo HTML_PageEditPage($keyNew, $menuLabel, $cateLabel);
	switch($_GET[ask])
	{
		case 1: echo HTML_PageAsk_Delete($pageLabel, $keyNew); break;
		case 4: echo HTML_PageAsk_Add($keyNew); break;
		case 5: echo HTML_PageAsk_Rename($pageLabel, $keyNew); break;
	}
	echo("<br/>".$cmspageReport."<br/></div></center>");
	break;
	
	// Content Edit
	case 6:
	$menuLabel = file_get_contents("MCMSR/site/".$_GET[menu]."/label.txt");
	$cateLabel = file_get_contents("MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/label.txt");
	$pageLabel = file_get_contents("MCMSR/site/".$_GET[menu]."/".$_GET[cate]."/".$_GET[page]."/label.txt");	
	
	switch($_GET[action])
	{
		case 1: $cmspageReport = ContentAction_Save(); break;
	}
	
	echo HTML_ContentEditPage($menuLabel, $cateLabel, $pageLabel, $keyNew);
	
	echo("<br/>".$cmspageReport."<br/></div></center>");
	
	echo "<br/><br/><a href=\"admin.php?cmspage=5&menu=".$_GET[menu]."&cate=".$_GET[cate]."&page=".$_GET[page]."&key=".$keyNew."\" title=\"Geri Dön\"><i>&lt;&lt; Geri Dön</i><a/>";
	break;
	
	case 100: //ŞİFRE DEĞİŞTİRME
	echo("
<div class=\"icerik\"><center><br/>
Yeni admin şifresini girin:<br/>
<form action=\"admin.php?cmspage=100&action=1&key=".$keyNew."\" method=\"post\" name=\"askass\">
<input name=\"newp\" type=\"password\" value=\"\" size=\"40\" maxlength=\"40\"/><br/>
<input type=\"submit\" value=\"Değiştir\" name=\"b1\">
</form>
</center>
	");
	if($_POST[newp] != null && $_GET[action]==1)
	{
		unlink("MCMSR/secured/pass.txt");
		$passwordInFiledottxt = fopen("MCMSR/secured/pass.txt", 'w');
		fwrite($passwordInFiledottxt, $_POST[newp]);
		fclose($passwordInFiledottxt);
		$passwordInFile = file_get_contents("MCMSR/secured/pass.txt");
		$cmspageReport="<span style=\"color:green\">Şifreyi değiştir de senden başkası demoyu kullanamasın de'mi çakaal ;)</center>"; 
	}
	else if($_GET[action]==1)
	{
		$cmspageReport="<span style=\"color:red\">Şifre değiştirilemedi, en az bir karakter yazmak zorundasınız...</center>";
	}
	echo("<br/>".$cmspageReport."<br/></center></div>");
	break; //END - ŞİFRE DEĞİŞTİRME

	case 101: //GÜVENLİ ÇIKIŞ
	GenerateNewKey();
	$cmspageReport="<span style=\"color:green\">Panelden güvenli bir şekilde çıkış yapılmıştır...</center>";
	echo("
<div class=\"icerik\"><center><br/>".$cmspageReport."<br/></center></div>
	");
	break; //END - GÜVENLİ ÇIKIŞ
}
///////////////////////////////////////////////////////////////////
////////////////////////////END IF PASS////////////////////////////
///////////////////////////////////////////////////////////////////
	break;
}

////////////
echo "<br/><br/>";
$scrReport = ftp_raw($ftpStream, "SITE CHMOD 0700 ".$MCMSR."/secured");
ftp_close($ftpStream);
////////////
?>

<?php 
$end = getTime(); 
echo "<div class=\"zaman\">".number_format(($end - $start),8)." sn.<br/>".$scrReport[0]."</span>";
?>

</body>
</html>