<?php
Use Sentiment\Analyzer;
$JWT_SECRET_KEY = 't1NP63m4wnBg6nyHYKfmc2TpCOGI4nss';
class RssController extends BaseController
{
   private function getToken()
   {
     return 't1NP63m4wnBg6nyHYKfmc2TpCOGI4nss';
   }
   public function sentimentAction()
   {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();
        //echo $arrQueryStringParams;
        //print_r($JWT_SECRET_KEY);
        if (!isset($arrQueryStringParams['t']) || $arrQueryStringParams['t'] != $this->getToken()) {
            $strErrorDesc = 'Invalid or missing token';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
            array('Content-Type: application/json', $strErrorHeader)
            );
        }
        if (!isset($arrQueryStringParams['rss_url']) ||  $arrQueryStringParams['rss_url']=="")
        { 
            $strErrorDesc = 'Invalid or missing RSS feed url';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
        $bd = (isset($arrQueryStringParams['bd']))?trim($arrQueryStringParams['bd'],'\'"'):1;// before in days
        $q = (isset($arrQueryStringParams['q']))?trim($arrQueryStringParams['q'],'\'"'):""; // comma separated multiple queries
        $rss_url = trim($arrQueryStringParams['rss_url'],'\'"'); // comma separated multy rss url's
        $result = (isset($arrQueryStringParams['return_rss']));// return find results if set , this is to preserve bandwith
        $feeds = explode(",",$rss_url);// if there is more rss feed will be separated by ","
        //Read each feed's items
        $entries = array();
        foreach($feeds as $feed) {
            $xml = simplexml_load_file($feed);
            $entries = array_merge($entries, $xml->xpath("//item"));
        }
        $tm = strtotime('-'.$bd.'days', time());
        $param = [$tm,$q];
        $entries = array_filter($entries,function ($a) use ($param){  
                        $srch = [];
                        if (strlen($param[1]) > 0)
                            $srch = explode(",",$param[1]);
                        if (strtotime($a->pubDate) > $param[0] )
                        {
                                foreach($srch as $s)
                                {
                                
                                    if (preg_match("/[^a-z0-9]".$s."/i", " ".$a->title." ") == 1 
                                                || preg_match("/[^a-z0-9]".$s."/i", " ".$a->description." ") == 1  )
                                    return true;
                                }
                                return count($srch) > 0?false:true;
                        }else
                            return false;
                    
                        } 
                    );
        $pattern = "/(?<=[^A-Z].[.?]) +(?=[A-Z])/";
        $analyzer = new Analyzer();	
        $cmp = 0.0;
        //echo "entries[".count($entries)."]\n";
        $data = [];
        $c = 1;
        foreach($entries as $entry)
        {
            $title = $entry->title;
            $dsc = $entry->description;
            
            $cmp += $analyzer->getSentiment($title)["compound"];
            //echo "title[".$title."]\n";
            //echo "descr[".$dsc."]\n";
            $c = 1;
            $feed = new stdClass;
            $feed->title = $title;
            $data[] = $feed ;
            if ($dsc != null)
            {
                $phrases = preg_split($pattern, $dsc);
                $dt = "";
                foreach($phrases as $phrase)
                {
                //	echo "phrase[".$phrase."]\n";
                    $cmp += $analyzer->getSentiment($phrase)["compound"];
                    $c++;
                    $dt .= $phrase;
                }// end for
                $feed->descr  = $dt;
                
            }// end if
            
        }// end for
        //echo "\n-------------------------------------------------------------------\n";
        //echo json_encode($data);
        //echo "\n--------------------------------------------------------------------\n";
        
        $res["news_count"] = count($entries);
        $res["rss"] = $rss_url;
        $res["newer_then"] = $bd;
        $res["query"] = $q;
        $res["data"] = ($result)?$data:"NA"; // to preserve data bandwidth
        $res["sentiment"] = $cmp/$c;
        $this->sendOutput(
            json_encode($res),
            array('Content-Type: application/json', 'HTTP/1.1 200 OK')
        );
   }
}
?>