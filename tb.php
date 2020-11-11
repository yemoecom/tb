<?php
header("Content-type: text/html; charset=UTF-8");
echo getejson();

//抓取数据
function getejson(){             
     $cookie = getcookie();                
     $tmp_url = "https://market.m.taobao.com/app/tb-windmill-app/ishopping/index";
     $Browser  = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36';                
     $appKey= 12574478;                                 
     $_m_h5_tk= get_word($cookie,'_m_h5_tk=', '_');//从cookie中取出_m_h5_tk，必须要去掉后面的部分
     $t =getMillisecond();//生成时间戳   
     $data ='{"type":"guang","id":"301740051","extParams":"{\"spm-cnt\":\"a310p.11570659\",\"spm-url\":\"a310p.11215598.tuijian.no_banner_2\",\"page\":\"guang\",\"product_type\":\"videointeract\",\"echoParam\":{}}"}';//请求的数据
     $url_data = urlencode($data);//请求的数据编码后要拼接到地址上。
     $headers = array('Content-type:application/x-www-form-urlencoded','Accept:application/json');                                      
     $sign=md5($_m_h5_tk."&".$t."&".$appKey."&".$data); //生成sign
     $url = "https://h5api.m.taobao.com/h5/mtop.mediainteraction.video.detail/1.0/?jsv=2.4.5&appKey=12574478&t=".$t."&sign=".$sign."&api=mtop.mediainteraction.video.detail&v=1.0&timeout=20000&data=".$url_data;
     $ch = curl_init($url);                
     curl_setopt($ch,CURLOPT_HEADER,0);
     curl_setopt($ch,CURLOPT_REFERER, $tmp_url);        
     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
     curl_setopt($ch, CURLOPT_USERAGENT, $Browser);
     curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
     curl_setopt($ch,CURLOPT_COOKIE,$cookie);  
     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
     $content = curl_exec($ch);                  
     curl_close($ch); 
return $content;
        }


//获取cookie
function getcookie(){
             $tmp_url = "https://market.m.taobao.com/app/tb-windmill-app/ishopping/index";//伪造来路
             $Browser  = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36';//模拟UA  这里用的是浏览器的             
             $cookie="";//初始化cookie
             $headers = array('Content-type:application/x-www-form-urlencoded','Accept:application/json');//发送请求的header
             for($j=0;$j<=2;$j++){  //需要请求两次，因为第一次访问失败之后才会生成cookie
             $url="https://h5api.m.taobao.com/h5/mtop.mediainteraction.video.detail/1.0/?appKey=12574478";//请求地址，必须带上这个默认的appkey
             $ch = curl_init($url);        
             curl_setopt($ch,CURLOPT_HEADER,1);//输出头部信息，cookie就包含其中
             curl_setopt($ch,CURLOPT_REFERER, $tmp_url);        
             curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
             curl_setopt($ch, CURLOPT_USERAGENT, $Browser);
             curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
             curl_setopt($ch,CURLOPT_COOKIE,$cookie);  
             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
             curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
             $content = curl_exec($ch);                  
             curl_close($ch);              
             $_m_h5_tk=get_word($content,'_m_h5_tk=', ';'); //取出_m_h5_tk            
             $_m_h5_tk_enc=get_word($content,'_m_h5_tk_enc=', ';');   //取出_m_h5_tk_enc 
             if($_m_h5_tk && $_m_h5_tk_enc){
             return "_m_h5_tk_enc=".$_m_h5_tk_enc."; _m_h5_tk=".$_m_h5_tk;
             }
	 }
}	

//从cookie中取出_m_h5_tk和_m_h5_tk_enc这两个值就行了。
function get_word($html,$star,$end){
        $pat = '/'.$star.'(.*?)'.$end.'/s';
        if(!preg_match_all($pat, $html, $mat)) {                
        }else{
                $wd= $mat[1][0];
        }        
        return $wd;
} 

//然后拼接系统当前时间的13位时间戳
function getMillisecond() {
                list($t1, $t2) = explode(' ', microtime());
                return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        }