<?php

ini_set( 'display_errors', '1' );

    //----------------------------------------------------------------
	function callback($buffer)
	{
		global $tag_match_array;
       // print "=101=======================================================\n<br>";
        //print_r($tag_match_array);
        //print "=102=======================================================\n<br>";
		//$sub_string_total="xx";
		$match_array=array();
		$inner_array=array();
		$search=0;
		$buffer_size=strlen($buffer);
		$query="";
		$str_match="";
		$cur_match=""; 
		$inner_match="";
		$start_count=0;
		$end_count=0;
		while($search<=$buffer_size){
		
		    $sub_string = substr($buffer, $search, 1);
			if($sub_string=="{"){
				$start_count++;
				$cur_match.=$sub_string;
			}elseif($sub_string=="}"){
				$end_count++;
				$cur_match.=$sub_string;
			}else{
				if($start_count>0){
					$cur_match.=$sub_string;
					$inner_match.=$sub_string;
				}
			}
			if(($start_count==2)&&($end_count==2)){
				$match_array[]=$cur_match;
				$inner_array[]=$inner_match;
				$cur_match="";
				$inner_match="";
				$start_count=0;
				$end_count=0;
			}
            $search++;
		}
		for($x=0;$x<count($match_array);$x++){
			if(isset($tag_match_array[$inner_array[$x]])){
				$query.="| ".$x." |\n ".$inner_array[$x]."\n--".$match_array[$x]."=>".$tag_match_array[$inner_array[$x]];//var_export($tag_match_array[$inner_array[$x]],true);
				$buffer=str_replace($match_array[$x], $tag_match_array[$inner_array[$x]], $buffer);
			}else{
				$buffer=str_replace($match_array[$x], "", $buffer);
			}
		}
		return $buffer;
	}

    function download($file_source, $file_target) {
        $rh = fopen($file_source, 'rb');
        $wh = fopen($file_target, 'wb');
        if ($rh===false || $wh===false) {
// error reading or opening file
           return true;
        }
        while (!feof($rh)) {
            if (fwrite($wh, fread($rh, 1024)) === FALSE) {
                   // 'Download error: Cannot write to file ('.$file_target.')';
                   return true;
               }
        }
        fclose($rh);
        fclose($wh);
        // No error
        return false;
    }

    function url_get_contents($url){
        $useragent="curl";
        $encoded="";
        if(count($_GET)>0){
            foreach($_GET as $name => $value) {
                $encoded .= urlencode($name).'='.urlencode($value).'&';
              }
        }
        if(count($_POST)>0){
            foreach($_POST as $name => $value) {
                $encoded .= urlencode($name).'='.urlencode($value).'&';
              }
        }
          
        $encoded = substr($encoded, 0, strlen($encoded)-1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile); // Cookie aware
        //curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile); // Cookie aware
        $result=curl_exec($ch);
        curl_close($ch);
        
        return $result;
    }

    
    function Display($RemoteServer,$LocalServer,$DisplayPage){
        $urldetails=$RemoteServer."?x=1&dcmshost=".$LocalServer."&dcmsuri=".$DisplayPage;	
        //print $urldetails;
        $retdata=url_get_contents($urldetails);
        return $retdata;
    
    }
	//echo "xxx";
    $message="";
    $DisplayPageArray=explode("/",$_SERVER['REQUEST_URI']);
    //$DisplayPage=$_SERVER['REQUEST_URI'];
    if(isset($_GET['uri'])){
        $DisplayPage=$_GET['uri'];
    }else{
        $DisplayPage=urlencode("/");
    }
    $OriginalDisplayPage=$DisplayPage;
    //print "-".$DisplayPage."-";
    $RemoteServer="w-d.biz/";
    $LocalHost=urlencode($_SERVER['HTTP_HOST']);
    $DisplayPage.="&LocalServer=".$LocalHost;
    $LocalServer="install.me";
    //print $DisplayPage."-".$RemoteServer."-".$LocalHost."-".$LocalServer."-\n<br>";
    
    
    
    $source_code=Display($RemoteServer,$LocalServer,$DisplayPage);
    
    $step=urldecode($OriginalDisplayPage);
    //$message.=$step;
    switch($step){
        case "/":
            $message='Step 1-';
            $filename = './install.zip';

            if (file_exists($filename)) {
                $message.="The file $filename has already been downloaded";
            } else {
                $file_url="http://assets.w-d.biz/downloads/Latest-BCMS_Distributed.zip";
                $file_target="./install.zip";
                download($file_url, $file_target);
            }
            
            

            $filename = './install.zip';

            if (file_exists($filename)) {
                $message.="The file $filename exists";
            } else {
                $message.="The file $filename does not exist";
            }
        break;
        case "/step-2/":
            $message.='Step 2-';
            $zip = new ZipArchive;
            if ($zip->open('./install.zip') === TRUE) {
                $zip->extractTo('.');
                $zip->close();
                $message.='ok';
            } else {
                $message.='failed';
            }
            $file_array=array();
            $file_array[]="./index.php";
            $file_array[]="./.htaccess";
            $file_array[]="./info.php";
            $file_array[]="./classes/clsDCMS.php";
            $file_array[]="./classes/info.php";
            $file_array[]="./cache";
            $file_array[]="./cache/cookies";
            foreach($file_array as $val){
                if (file_exists($val)) {
                    $message.="$filename exists<br>";
                } else {
                    $message.="$filename does not exist<br>";
                }
            }
        break;

    }
    //print "++".$message."++";

    $tag_match_array["message"]=$message;
    $source_code=callback($source_code);
    echo $source_code;


?>