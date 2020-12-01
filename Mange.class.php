<?php
class Mange{
	public $r=array();
	public $_is_cookie=0;
	public function getejson($h5api,$r=array()){ 
		global $cookie;
		//参数处理
		$h5api=str_replace('jsonp','json',$h5api);
		if(stripos($h5api,'&sign') || stripos($h5api,'&t=')){
			$h5api=preg_replace('/&sign=\w+/','',$h5api);
			$h5api=preg_replace('/&t=\d+/','',$h5api);
			$h5api=preg_replace('/&callback=\w+/','',$h5api);
		}
		if(stripos($h5api,'&data')){
			preg_match('/&data=(.*7D$)/',$h5api,$data);
			$h5api=preg_replace('/&data=.*7D$/','',$h5api);
			$data=$data[1];
		}
		if(empty($data)) exit("参数的data不存在或者错误");
		if(strpos($data,'%3A')) $data = urldecode($data);
		
		
		if(!empty($r)) $data=$this->editdata($r,$data);//条件查询
		$cookie = $this->getcookie();//获取cookie
		if($cookie=='' || $_is_cookie==1){//读取本地cookie 在浏览器登陆淘宝后在console里输入document.cookie
			$cookie = "cookie.txt";
			if(file_exists($cookie)){
				if(filesize($cookie)=='') exit('cookie内容为空');
				$fp = fopen($cookie,"r")or die("文件不存在");
				$cookie = fread($fp,filesize($cookie));
				if(empty($cookie)) exit('cookie文件为空或不存在');
				fclose($fp);
			}else{
				exit("目录下cookie.txt不存在或者为空，请更新cookie");
			}
		}
		if(empty($cookie)) exit("你的cookie已失效！请重新获取");		        
		$appKey= 12574478;                                 
		$_m_h5_tk= $this->get_word($cookie,'_m_h5_tk=', '_');//从cookie中取出_m_h5_tk，必须要去掉后面的部分
		$t =$this->getMillisecond();//生成时间戳   
		$url_data = urlencode($data);                                    
		$sign=md5($_m_h5_tk."&".$t."&".$appKey."&".$data); //生成sign
		$url = $h5api."&t=".$t."&sign=".$sign."&data=".$url_data;
		$d=$this->curl($url);//获取数据
		//if(strpos($d,'RGV587_ERROR') || strpos($d,'令牌过期')) exit('1、你的cookie已失效！2、不支持此接口！3、你的data里参数错误');
		return $d;
        }
		
	
	
	public function curl($url){
		global $cookie;
		$tmp_url = "https://market.m.taobao.com/app/tb-windmill-app/ishopping/index";
		$Browser  = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36';
		$headers = array('Content-type:application/x-www-form-urlencoded','Accept:application/json');
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
	public function getcookie(){
		global $url;
	 $tmp_url = "https://market.m.taobao.com/app/tb-windmill-app/ishopping/index";//伪造来路
	 $Browser  = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36';//模拟UA  这里用的是浏览器的             
	 $cookie="";//初始化cookie
	 $headers = array('Content-type:application/x-www-form-urlencoded','Accept:application/json');//发送请求的header
		 for($j=0;$j<=2;$j++){  //需要请求两次，因为第一次访问失败之后才会生成cookie
			 $url=" http://h5api.m.taobao.com/h5/mtop.taobao.detail.getdesc/6.0/?data=%7B%22id%22:%22565317851627%22%7D";//请求地址，必须带上这个默认的appkey
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
			 $_m_h5_tk=$this->get_word($content,'_m_h5_tk=', ';'); //取出_m_h5_tk            
			 $_m_h5_tk_enc=$this->get_word($content,'_m_h5_tk_enc=', ';');   //取出_m_h5_tk_enc 
		 if($_m_h5_tk && $_m_h5_tk_enc){
		 return "_m_h5_tk_enc=".$_m_h5_tk_enc."; _m_h5_tk=".$_m_h5_tk;
			}else{
				return $content;
			}
		 }
	}
	//获取店铺id
	public function getshop($url){
		$d=$this->requery($url);
		$arr=array();
		preg_match('/"user_nick":\s+"(.*?)",/',$d,$a);
		preg_match('/userId:\'(.*?)\',/',$d,$b);
		if(empty($b)){
			preg_match('/userId:\s+\'(.*?)\',/',$d,$b);
			preg_match('/"shopId":\s+"(.*?)",/',$d,$c);
		}
		$arr['nickName']=urldecode($a[1]);
		$arr['userId']=$b[1];
		$arr['shopId']=$c[1];
		$url='http://hdc1.alicdn.com/asyn.htm?userId='.$arr['userId'];
		$contents=file_get_contents("compress.zlib://".$url);
		$contents = iconv("gb2312", "utf-8//IGNORE",$contents);
		$contents=str_replace('\r\n','',$contents);
		$contents=str_replace('\n\n','',$contents);
		$contents=str_replace('\n','',$contents);
		$contents=str_replace('\\','',$contents);
		$contents=str_replace('//','http://',$contents);
		preg_match('/<label>\s+公 司 名：\s+<\/label>\s+<div class="right">\s+(.*?)\s+<\/div>/',$contents,$gs);
		preg_match('/<label>\s+所 在 地：\s+<\/label>\s+<div class="right">\s+(.*?)\s+<\/div>/',$contents,$dq);
		$arr['gongsi']=$gs[1];
		$arr['diqu']=$dq[1];
		return $arr;
	}
	
	//处理店铺信息
	public function setshopinfo($info){
		$arr=array();
		$info=json_decode($info,true);
		$arr['userId']=$info['data']['seller']['userId'];
		$arr['taoShopUrl']=$info['data']['seller']['taoShopUrl'];
		$arr['shopId']=$info['data']['seller']['shopId'];
		$arr['shopName']=$info['data']['seller']['shopName'];
		$arr['fans']=$info['data']['seller']['fans'];
		$arr['shopCard']=$info['data']['seller']['shopCard'];
		$arr['allItemCount']=$info['data']['seller']['allItemCount'];
		$arr['sellerType']=$info['data']['seller']['sellerType'];
		$arr['sellerNick']=$info['data']['seller']['sellerNick'];
		$arr['creditLevel']=$info['data']['seller']['creditLevel'];
		$arr['starts']=$info['data']['seller']['starts'];
		$arr['fbt2User']=$info['data']['seller']['fbt2User'];
		$arr['goodRatePercentage']=$info['data']['seller']['goodRatePercentage'];
		$arr['certText']=$info['data']['seller']['certText'];
		$arr['evaluates']=$info['data']['seller']['evaluates'];
		return $arr;
	}
	
//从cookie中取出_m_h5_tk和_m_h5_tk_enc这两个值就行了。
	public function get_word($html,$star,$end){
        $pat = '/'.$star.'(.*?)'.$end.'/s';
        if(!preg_match_all($pat, $html, $mat)) {                
        }else{
                $wd= $mat[1][0];
        }        
        return $wd;
	} 

//然后拼接系统当前时间的13位时间戳
	public function getMillisecond() {
                list($t1, $t2) = explode(' ', microtime());
                return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        }
		
	 
	public function requery($url){
			$header = array (
  0 => 'dnt: 1',
  1 => 'accept-encoding: gzip, deflate, br',
  2 => 'accept-language: zh-CN',
  3 => 'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.81 Safari/537.36 Maxthon/5.3.8.2000',
  4 => 'accept: */*',
  5 => 'referer: https://detail.tmall.com/item.htm?spm=a230r.1.14.345.331c6800UHV8hh&id=591148184469&ns=1&abbucket=8&sku_properties=5919063:6536025',
  6 => 'authority: rate.tmall.com',
);
    $postData = '';
     $cookie = 'cookie: hng=CN%7Czh-CN%7CCNY%7C156; cna=iyLyFtr6JmoCAXJmm/iSFV71; lid=jilaweigc; tk_trace=1; xlly_s=1; uc1=cookie14=Uoe0abqK4xIr1g%3D%3D; t=ebb13f797989ad6db51d05f4f110b69b; tracknick=jilaweigc; lgc=jilaweigc; _tb_token_=f3665713b3617; _m_h5_tk=b01dbb592df88186775b2e2538b183ea_1604910425717; _m_h5_tk_enc=46a415b3d6e79ea5c2a275fe2600ef16; x5sec=7b22726174656d616e616765723b32223a2237363865303932346166376166633838636534626433386637306136636332654349717a6f2f3046454a6141785a5050302b715243773d3d227d; isg=BLm5VNPbwgDc_59QcI-NN9d8yCWTxq14NihTbtvuNeBfYtn0Ixa9SCcw5GaUQUWw; tfstk=cBafB9wFrKvX_9SwuS1rg8QavB3OwPRI1iM0GoeD_CYrxv10pek3QSEMB_yoN; l=eB_pjiXPQHrhexsCBOfanurza77OSIRYYuPzaNbMiOCPO3fB59nfWZSToAL6C3GVh6WBR3JLTTaWBeYBq7VonxvTKZ1pKpDmn'; //需要cookie的话去掉这行的注释
    $timeout = 10;

    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);       //返回数据不直接输出
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");        //指定gzip压缩
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);    //302/301
    //SSL
    if(substr($url, 0, 8) === 'https://') {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //error:14077458:SSL routines:SSL23_GET_SERVER_HELLO:reason(1112)解决
        //值有0-6，请参考手册，值1不行试试其他值
        //curl_setopt($ch, CURLOPT_SSLVERSION, 1);
    }
    //post数据
    if(!empty($postData)) {
        curl_setopt($ch, CURLOPT_POST, 1);               //发送POST类型数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); //POST数据，$post可以是数组（multipart/form-data），也可以是拼接参数串（application/x-www-form-urlencoded）
    }
    if(!empty($cookie)) {
        $header[] = $cookie;
    }
    if(!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);     //使用header头信息
    }
    //超时时间
    curl_setopt($ch, CURLOPT_TIMEOUT, (int)$timeout);
    //执行
    $content = curl_exec($ch);
    if($error = curl_error($ch)) {
        //log error
        error_log($error);
    }
    curl_close($ch);
	$content=preg_replace('/\w+\d+\(/','',$content);
	$content=preg_replace('/\w+\d+\s+=\[/','',$content);
	$content=str_replace('_back="fp_midtop=&firstpage_pushleft=0"','',$content);
	$content=preg_replace('/\]/i','',$content);
    $content=str_replace('[]}})','[]}}',$content);
	

			// $content 是请求结果
			return $content;
	}
	public function requerys($url){
			$header = array (
  0 => 'Connection: keep-alive',
  1 => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36',
  2 => 'Accept: */*',
  3 => 'Sec-Fetch-Site: same-origin',
  4 => 'Sec-Fetch-Mode: no-cors',
  5 => 'Referer: https://s.taobao.com/search?q=&js=1&initiative_id=staobaoz_20201121&ie=utf8&bcoffset=1&ntoffset=1&p4ppushleft=1%2C48&s=88',
  6 => 'Accept-Encoding: gzip, deflate, br',
  7 => 'Accept-Language: zh-CN,zh;q=0.9',
);
    $postData = '';
     $cookie = 'cookie: hng=CN%7Czh-CN%7CCNY%7C156; cna=iyLyFtr6JmoCAXJmm/iSFV71; lid=ouku; enc=t%2FiOrUQHxydFsg8cFTUM6%2BLlsV1TK6a8zO9ZNG1kb6HFhbLcoArSW5VwaB24kNcuG5CLP%2BnCDIKoYX9q7a1JhQ%3D%3D; xlly_s=1; _m_h5_tk=7f412d4c94ddede44fed189c59b24a44_1605867999834; _m_h5_tk_enc=a94fa66c9bb8a7846dabb434508df661; l=eB_pjiXPQHrhesdDKOfwourza77OSIRAguPzaNbMiOCP_n1J5cfAWZ7kTt8vC3GVh64kR3JLTTaWBeYBquqonxvOa6Fy_Ckmn; tfstk=clQCBq64SvDBJQrr86NwT-Yv8RYFZxlXNk9hOMpYcGEbVUfCiK32nT5dqYFNIC1..; isg=BDg4WWQWkyzX1f5zGYgcxM4TCebKoZwrX5fyzXKphHMmjdh3GrFsu04vQYU93VQD; sgcookie=E100IRK%2BEzdkk0%2FIcmvFLQR8v3i4lN9mfPa8Brc3NlfDD%2FGOivNfLxAVekoD2H4e%2Ble821ZIlLMQ0CxlPHIJ8ss4Vw%3D%3D; t=ebb13f797989ad6db51d05f4f110b69b; uc3=lg2=VT5L2FSpMGV7TQ%3D%3D&id2=UoCLFPxf1Xwh&nk2=CdrmDcu2dGuK&vt3=F8dCufwryGwkXT3qv6o%3D; tracknick=ouku; uc4=nk4=0%40C%2BXjpALcWtv3mB2RgFSx9qmUtWI%3D&id4=0%40UOg3szuCuEucwNTs06SamlrgiRQ%3D; lgc=ouku; _tb_token_=5868f8957ee3; cookie2=1c386ce346efb9723779fe2762c937da';//需要cookie的话去掉这行的注释
    $timeout = 10;

    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);       //返回数据不直接输出
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");        //指定gzip压缩
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);    //302/301
    //SSL
    if(substr($url, 0, 8) === 'https://') {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //error:14077458:SSL routines:SSL23_GET_SERVER_HELLO:reason(1112)解决
        //值有0-6，请参考手册，值1不行试试其他值
        //curl_setopt($ch, CURLOPT_SSLVERSION, 1);
    }
    //post数据
    if(!empty($postData)) {
        curl_setopt($ch, CURLOPT_POST, 1);               //发送POST类型数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); //POST数据，$post可以是数组（multipart/form-data），也可以是拼接参数串（application/x-www-form-urlencoded）
    }
    if(!empty($cookie)) {
        $header[] = $cookie;
    }
    if(!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);     //使用header头信息
    }
    //超时时间
    curl_setopt($ch, CURLOPT_TIMEOUT, (int)$timeout);
    //执行
    $content = curl_exec($ch);
    if($error = curl_error($ch)) {
        //log error
        error_log($error);
    }
    curl_close($ch);
	$content=preg_replace('/\w+\d+\(/','',$content);
	$content=preg_replace('/\w+\d+\s+=\[/','',$content);
	$content=str_replace('_back="fp_midtop=&firstpage_pushleft=0"','',$content);
	$content=preg_replace('/(\])$/i','',$content);
    $content=str_replace('[]}})','[]}}',$content);
	$content=str_replace('}});','}}',$content);
	

			// $content 是请求结果
			return $content;
	}
	
	//淘宝的中文转码
	public function parseurl($url=""){
		$url = rawurlencode(mb_convert_encoding($url, 'gb2312', 'utf-8'));
		$a = array("%3A", "%2F", "%40");
		$b = array(":", "/", "@");
		$url = str_replace($a, $b, $url);
		return $url;
	}
	
	public function curls($url){
    $header = array (
  0 => 'authority: scud.m.tmall.com',
  1 => 'cache-control: max-age=0',
  2 => 'upgrade-insecure-requests: 1',
  3 => 'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.81 Safari/537.36 Maxthon/5.3.8.2000',
  4 => 'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
  5 => 'dnt: 1',
  6 => 'accept-encoding: gzip, deflate, br',
  7 => 'accept-language: zh-CN',
);
    $postData = '';
     $cookie = 'cookie: hng=CN%7Czh-CN%7CCNY%7C156; cna=iyLyFtr6JmoCAXJmm/iSFV71; lid=ouku; enc=t%2FiOrUQHxydFsg8cFTUM6%2BLlsV1TK6a8zO9ZNG1kb6HFhbLcoArSW5VwaB24kNcuG5CLP%2BnCDIKoYX9q7a1JhQ%3D%3D; xlly_s=1; _m_h5_tk=7f412d4c94ddede44fed189c59b24a44_1605867999834; _m_h5_tk_enc=a94fa66c9bb8a7846dabb434508df661; l=eB_pjiXPQHrhesdDKOfwourza77OSIRAguPzaNbMiOCP_n1J5cfAWZ7kTt8vC3GVh64kR3JLTTaWBeYBquqonxvOa6Fy_Ckmn; tfstk=clQCBq64SvDBJQrr86NwT-Yv8RYFZxlXNk9hOMpYcGEbVUfCiK32nT5dqYFNIC1..; isg=BDg4WWQWkyzX1f5zGYgcxM4TCebKoZwrX5fyzXKphHMmjdh3GrFsu04vQYU93VQD; sgcookie=E100IRK%2BEzdkk0%2FIcmvFLQR8v3i4lN9mfPa8Brc3NlfDD%2FGOivNfLxAVekoD2H4e%2Ble821ZIlLMQ0CxlPHIJ8ss4Vw%3D%3D; t=ebb13f797989ad6db51d05f4f110b69b; uc3=lg2=VT5L2FSpMGV7TQ%3D%3D&id2=UoCLFPxf1Xwh&nk2=CdrmDcu2dGuK&vt3=F8dCufwryGwkXT3qv6o%3D; tracknick=ouku; uc4=nk4=0%40C%2BXjpALcWtv3mB2RgFSx9qmUtWI%3D&id4=0%40UOg3szuCuEucwNTs06SamlrgiRQ%3D; lgc=ouku; _tb_token_=5868f8957ee3; cookie2=1c386ce346efb9723779fe2762c937da'; //需要cookie的话去掉这行的注释
    $timeout = 10;

    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);       //返回数据不直接输出
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");        //指定gzip压缩
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);    //302/301
    //SSL
    if(substr($url, 0, 8) === 'https://') {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //error:14077458:SSL routines:SSL23_GET_SERVER_HELLO:reason(1112)解决
        //值有0-6，请参考手册，值1不行试试其他值
        //curl_setopt($ch, CURLOPT_SSLVERSION, 1);
    }
    //post数据
    if(!empty($postData)) {
        curl_setopt($ch, CURLOPT_POST, 1);               //发送POST类型数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); //POST数据，$post可以是数组（multipart/form-data），也可以是拼接参数串（application/x-www-form-urlencoded）
    }
    if(!empty($cookie)) {
        $header[] = $cookie;
    }
    if(!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);     //使用header头信息
    }
    //超时时间
    curl_setopt($ch, CURLOPT_TIMEOUT, (int)$timeout);
    //执行
    $content = curl_exec($ch);
    if($error = curl_error($ch)) {
        //log error
        error_log($error);
    }
    curl_close($ch);

    // $content 是请求结果

		return $content;
	}
	
	//毫秒Unix时间戳
	static public function getMsec() {
        list($msec, $sec) = explode(' ', microtime());
        return intval(((float)$msec + (float)$sec) * 1000);
    }
	
	static public function nyr(){
		return date('Ymd',time());
	}
	
	static public function decodeUnicode($str){
		return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
        create_function(
            '$matches',
            'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
        ),
        $str);
	}
	
	//拼音生成
	public function pinyin($_String, $_Code='gb2312'){
		$_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha".
		"|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|".
		"cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er".
		"|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui".
		"|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang".
		"|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang".
		"|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue".
		"|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne".
		"|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen".
		"|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang".
		"|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|".
		"she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|".
		"tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu".
		"|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you".
		"|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|".
		"zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
		$_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990".
		"|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725".
		"|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263".
		"|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003".
		"|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697".
		"|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211".
		"|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922".
		"|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468".
		"|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664".
		"|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407".
		"|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959".
		"|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652".
		"|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369".
		"|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128".
		"|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914".
		"|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645".
		"|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149".
		"|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087".
		"|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658".
		"|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340".
		"|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888".
		"|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585".
		"|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847".
		"|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055".
		"|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780".
		"|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274".
		"|-10270|-10262|-10260|-10256|-10254";
		$_TDataKey = explode('|', $_DataKey);
		$_TDataValue = explode('|', $_DataValue);
		$_Data = (PHP_VERSION>='5.0') ? array_combine($_TDataKey, $_TDataValue) : $this->_Array_Combine($_TDataKey, $_TDataValue);
		arsort($_Data);
		reset($_Data);
		if($_Code != 'gb2312') $_String = $this->_U2_Utf8_Gb($_String);
		$_Res = '';
		for($i=0; $i<strlen($_String); $i++)
		{
		$_P = ord(substr($_String, $i, 1));
		if($_P>160) { $_Q = ord(substr($_String, ++$i, 1)); $_P = $_P*256 + $_Q - 65536; }
		$_Res .= $this->_Pinyin($_P, $_Data);
		}
		return preg_replace("/[^a-z0-9]*/", '', $_Res);
		}
		public function _Pinyin($_Num, $_Data){
		if ($_Num>0 && $_Num<160 ) return chr($_Num);
		elseif($_Num<-20319 || $_Num>-10247) return '';
		else {
		foreach($_Data as $k=>$v){ if($v<=$_Num) break; }
		return $k;
		}
		}
		
		public function _U2_Utf8_Gb($_C){
		$_String = '';
		if($_C < 0x80) $_String .= $_C;
		elseif($_C < 0x800)
		{
		$_String .= chr(0xC0 | $_C>>6);
		$_String .= chr(0x80 | $_C & 0x3F);
		}elseif($_C < 0x10000){
		$_String .= chr(0xE0 | $_C>>12);
		$_String .= chr(0x80 | $_C>>6 & 0x3F);
		$_String .= chr(0x80 | $_C & 0x3F);
		} elseif($_C < 0x200000) {
		$_String .= chr(0xF0 | $_C>>18);
		$_String .= chr(0x80 | $_C>>12 & 0x3F);
		$_String .= chr(0x80 | $_C>>6 & 0x3F);
		$_String .= chr(0x80 | $_C & 0x3F);
		}
		return iconv('UTF-8', 'GB2312', $_String);
		}
		
		public function _Array_Combine($_Arr1, $_Arr2){
		for($i=0; $i<count($_Arr1); $i++) $_Res[$_Arr1[$i]] = $_Arr2[$i];
		return $_Res;
	}
	
	
	//数组替换
	public function arr_replace($arr){
		foreach($arr as $k=>$v){
			if(preg_match('/\d{10,}/',$v,$arrr)){ 
				$a=array_search($arrr[0],$arr);
				$arr['itemid']=$arr[$a];
				 unset($arr[$a]);
			}
			if(preg_match('/(.*com.*)/',$v,$arrr)){
				$b=array_search($arrr[0],$arr);
				$arr['url']=$arr[$b];
				 unset($arr[$b]);
			}
			if(preg_match('/\d{1,4}/',$v,$arrr)){
				$c=array_search($arrr[0],$arr);
				$arr['page']=$arr[$c];
				 unset($arr[$c]);
			}
			if(preg_match('/[\x{4e00}-\x{9fa5}]+/u',$v,$arrr)){
				$d=array_search($arrr[0],$arr);
				$arr['kw']=$arr[$d];
				 unset($arr[$d]);
			}
		}
		return $arr;
	}
	//处理data数据 $r是一个数组
	public function editdata($r,$data){
		if(!is_array($r)) exit('检查你的参数是否是数组');
		foreach($r as $k=>$v){
			if(stripos($data,$k)){
				$data=preg_replace('/"'.$k.'":".*"/','"'.$k.'":"'.$v.'"',$data);
					}
		}
		return $data;
	}
}
