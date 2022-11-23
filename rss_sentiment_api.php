<?php
require 'vendor/autoload.php';
Use Sentiment\Analyzer;
header("Content-Type:application/json");
$JWT_SECRET_KEY = 't1NP63m4wnBg6nyHYKfmc2TpCOGI4nss';
$arr_err = array("errCode" => 400,"errMsg" =>"Invalid Request");
$res = array("rss" => "","newer_then" => "0","query" => "","sentiment" => "");
if (!isset($_GET['t']) || (trim($_GET['t'],'\'"')!=$JWT_SECRET_KEY))
{
	$arr_err["errMsg"] = "Missing Token";
	echo json_encode($arr_err);
	//echo "\n".$_GET['t'];
	//echo "\n".$JWT_SECRET_KEY;
	return;
}
if (!isset($_GET['rss_url']) || (isset($_GET['rss_url']) && $_GET['rss_url']==""))
{ 
    $arr_err["errMsg"] = "Missing RSS Source";
	echo json_encode($arr_err);
	return;
}
$bd = (isset($_GET['bd']))?trim($_GET['bd'],'\'"'):1;
$q = (isset($_GET['q']))?trim($_GET['q'],'\'"'):""; // comma separated multiple queries
$rss_url = trim($_GET['rss_url'],'\'"'); // comma separated multy rss url's

response($rss_url,$bd,$q);
function response($rss_url,$bd,$q){
	/*if ($bd == null || $bd == "")
		$bd = 1;
	if ($q == null)
		$q = "";
	*/
	$feeds = explode(",",$rss_url);// if there is more rss feed will be separated by ","
	//Read each feed's items
	$entries = array();
	foreach($feeds as $feed) {
		$xml = simplexml_load_file($feed);
		$entries = array_merge($entries, $xml->xpath("//item"));
	}
	$tm = strtotime('-'.$bd.'days', time());
	$param = [$tm,$q];
	$entries = array_filter($entries,function ($a) use ($param)
						{  $srch[];
						   if (strlen($param[1]) > 0)
							   $srch = explode(",",$param[1]);
						   if (strtotime($a->pubDate) > $param[0] )
						   {
							    foreach($srch as $s)
								{
								  if ((strpos(strtolower($a->title), strtolower($s)) !== false)
          									|| ($a->description != null && strpos(strtolower($a->description), strtolower($s)) !== false))
										return true;
								}
								return count($srch) > 0?false:true;
						   }else
							   return false;
						   // echo "pubdate[".($a->description)."]\n";
							/*
							 return (strtotime($a->pubDate) > $param[0] 
							        && 
									(strlen($param[1]) == 0 || (strlen($param[1]) > 0 && ((strpos(strtolower($a->title), strtolower($param[1])) !== false)
          									|| ($a->description != null && strpos(strtolower($a->description), strtolower($param[1])) !== false)))));
					        */
					    } 
					);
	$pattern = "/(?<=[^A-Z].[.?]) +(?=[A-Z])/";
	$analyzer = new Analyzer();
	$cmp = 0.0;
	//echo "entries[".count($entries)."]\n";
	$data = [];
	foreach($entries as $entry)
	{
		$title = $entry->title;
		$dsc = $entry->description;
		
		$cmp += $analyzer->getSentiment($title)["compound"];
		//echo "title[".$title."]\n";
		$data[] = ["title" => $title , "desc" => $dsc];
		$c = 1;
		if ($dsc != null)
		{
			$phrases = preg_split($pattern, $dsc);
			foreach($phrases as $phrase)
			{
			//	echo "phrase[".$phrase."]\n";
				$cmp += $analyzer->getSentiment($phrase)["compound"];
				$c++;
			}// end for
			$data["desc"] = $phrases;
		}// end if
	}
	$res["news_count"] = count($entries);
	$res["rss"] = $rss_url;
	$res["newer_then"] = $bd;
	$res["query"] = $q;
	$res["data"] = $data;
	$res["sentiment"] = $cmp/$c;
	echo json_encode($res);
}
?>