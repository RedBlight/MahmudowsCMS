<?
ini_set('display_errors', 1);

function formatUrl( $string, $separator = '-' )
{
    $accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
    $special_cases = array( '&' => 'and', "'" => '');
    $string = mb_strtolower( trim( $string ), 'UTF-8' );
    $string = str_replace( array_keys($special_cases), array_values( $special_cases), $string );
    $string = preg_replace( $accents_regex, '$1', htmlentities( $string, ENT_QUOTES, 'UTF-8' ) );
    $string = preg_replace("/[^a-z0-9]/u", "$separator", $string);
    $string = preg_replace("/[$separator]+/u", "$separator", $string);
    return $string;
}

$currentMenuName = "";
$currentCateName = "";
$currentPageName = "";
$currentMenuId = 1;
$currentCateId = 1;
$currentPageId = 1;

$urlList = explode( "/", $_SERVER[ 'REQUEST_URI' ] );
if( isset( $urlList[ 1 ] ) )
{
	$currentMenuName = explode( "?", $urlList[ 1 ] );
	$currentMenuName = $currentMenuName[ 0 ];
}
if( isset( $urlList[ 2 ] ) )
{
	$currentCateName = explode( "?", $urlList[ 2 ] );
	$currentCateName = $currentCateName[ 0 ];
}
if( isset( $urlList[ 3 ] ) )
{
	$currentPageName = explode( "?", $urlList[ 3 ] );
	$currentPageName = $currentPageName[ 0 ];
}

$menuLabelList = array();
$menuNameList = array();
$cateLabelList = array();
$cateNameList = array();
$pageLabelList = array();
$pageNameList = array();

$filepath = "MCMSR/site/menucount.txt";
$menuN = file_exists( $filepath ) ? intval( file_get_contents( $filepath ) ) : 0;
for( $i = 1; $i <= $menuN; $i++ )
{
	$filepath = "MCMSR/site/" . $i . "/label.txt";
	$menuLabelList[ $i ] = file_exists( $filepath ) ? file_get_contents( $filepath ) : "";
	$menuNameList[ $i ] = formatUrl( $menuLabelList[ $i ] );
	
	if( $menuNameList[ $i ] == $currentMenuName )
	{
		$currentMenuId = $i;
	}
	
	$cateLabelList[ $i ] = array();
	$cateNameList[ $i ] = array();
	$pageLabelList[ $i ] = array();
	$pageNameList[ $i ] = array();
	
	$filepath = "MCMSR/site/" . $i . "/catecount.txt";
	$cateN = file_exists( $filepath ) ? intval( file_get_contents( $filepath ) ) : 0;
	for( $j = 1; $j <= $cateN; $j++ )
	{
		$filepath = "MCMSR/site/" . $i . "/" . $j . "/label.txt";
		$cateLabelList[ $i ][ $j ] = file_exists( $filepath ) ? file_get_contents( $filepath ) : "";
		$cateNameList[ $i ][ $j ] = formatUrl( $cateLabelList[ $i ][ $j ] );
	
		if( $currentMenuId == $i && $cateNameList[ $i ][ $j ] == $currentCateName )
		{
			$currentCateId = $j;
		}
		
		$pageLabelList[ $i ][ $j ] = array();
		$pageNameList[ $i ][ $j ] = array();
		
		$filepath = "MCMSR/site/" . $i . "/" . $j . "/pagecount.txt";
		$pageN = file_exists( $filepath ) ? intval( file_get_contents( $filepath ) ) : 0;
		for( $k = 1; $k <= $pageN; $k++ )
		{
			$filepath = "MCMSR/site/" . $i . "/" . $j . "/" . $k . "/label.txt";
			$pageLabelList[ $i ][ $j ][ $k ] = file_exists( $filepath ) ? file_get_contents( $filepath ) : "";
			$pageNameList[ $i ][ $j ][ $k ] = formatUrl( $pageLabelList[ $i ][ $j ][ $k ] );
	
			if( $currentMenuId == $i && $currentCateId == $j && $pageNameList[ $i ][ $j ][ $k ] == $currentPageName )
			{
				$currentPageId = $k;
			}
		}
	}
}

$filepath = "MCMSR/site/menucount.txt";
$menuCount = file_exists( $filepath ) ? intval( file_get_contents( $filepath ) ) : 0;
$filepath = "MCMSR/site/" . $currentMenuId . "/catecount.txt";
$cateCount = file_exists( $filepath ) ? intval( file_get_contents( $filepath ) ) : 0;
$filepath = "MCMSR/site/" . $currentMenuId . "/" . $currentCateId . "/pagecount.txt";
$pageCount = file_exists( $filepath ) ? intval( file_get_contents( $filepath ) ) : 0;
$menuLabel = $menuLabelList[ $currentMenuId ];
$cateLabel = $cateLabelList[ $currentMenuId ][ $currentCateId ];
$pageLabel = $pageLabelList[ $currentMenuId ][ $currentCateId ][ $currentPageId ];

?>

<!DOCTYPE html> 
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta http-equiv="pragma" content="no-cache">
	<meta http-equiv="cache-control" content="no-cache">
	<meta http-equiv="expires" content="0">
	<meta http-equiv="content-language" content="en" />
	
	<title> <? echo $pageLabel; ?> - MahmudowsCMS </title>
	<base href="/" />
	
	<meta name="keywords" content="mahudows, mahmudowsCMS, content management system">
	<meta name="description" content="A very simple content management system.">
	<meta name="author" content="Mahmut Akkuş">
	
	<link rel="shortcut icon" href="/MCMSR/img/favicon.png">
	<link rel="icon" type="image/png" href="/MCMSR/img/favicon.png">
	<link rel="stylesheet" type="text/css" href="/MCMSR/css/index.css" />

</head>

<body>


<div class="logo">
  <img src="/MCMSR/img/logo.png" />
</div>


<div class="topmenu">
<?
for( $i = 1; $i <= $menuCount; $i++ )
{
	$linkUrl = "/" . $menuNameList[ $i ];
	$linkLabel = $menuLabelList[ $i ];
	$btnClass = $i === $currentMenuId ? "btn-in" : "btn";
	echo '<a href="' . $linkUrl . '"><div class="' . $btnClass . '">' . $linkLabel . '</div></a>';
}
?>
</div>


<div class="container">
	<div class="container-left">
	<?
	for( $i = 1; $i <= $cateCount; $i++ )
	{
		$cateLabel = $cateLabelList[ $currentMenuId ][ $i ];
		echo '<div class="grouper">' . $cateLabel . '</div>';
		$filepath = "MCMSR/site/".$currentMenuId."/".$i."/pagecount.txt";
		$pageCountCate = file_exists( $filepath ) ? intval( file_get_contents( $filepath ) ) : 0;
		for( $j = 1; $j <= $pageCountCate; $j++ )
		{
			$linkUrl = '/' . $menuNameList[ $currentMenuId ] . '/' . $cateNameList[ $currentMenuId ][ $i ] . '/' . $pageNameList[ $currentMenuId ][ $i ][ $j ];
			$linkLabel = $pageLabelList[ $currentMenuId ][ $i ][ $j ];
			$btnClass = ( $j === $currentPageId && $i === $currentCateId ) ? "btn-in" : "btn";
			$btnMarker = ( $j === $currentPageId && $i === $currentCateId ) ? "&gt;" : "&#8226;";
			echo '<a href="' . $linkUrl . '"><div class="' . $btnClass . '">' . $btnMarker . ' ' . $linkLabel . '</div></a>';
		}
	}
	?>
	</div><div class="container-mid">

		<div class="content-wrap">
		
			<div class="title"><? echo $cateLabelList[ $currentMenuId ][ $currentCateId ]; ?></div>
			<div class="title-sub">-- <? echo $pageLabelList[ $currentMenuId ][ $currentCateId ][ $currentPageId ]; ?> --</div>
			<div class="content">
			<?
				$filepath = "MCMSR/site/" . $currentMenuId . "/" . $currentCateId . "/" . $currentPageId . "/content.txt";
				if( file_exists( $filepath ) )
				{
					include( $filepath );
				}
			?>
			</div>
		
		</div>
		
		<div class="footer">&copy; 2012 MahmudowsCMS - Mahmut Akkuş </div>

	</div><div class="container-right">
	<a href="https://github.com/RedBlight/MahmudowsCMS"><img src="/MCMSR/img/github.png" /></a>
	</div>
</div>








</body>
</html>