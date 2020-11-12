<?php
class Mange{
	public $r=array();
	public $_is_cookie=1;
	public $cookie=''; //如果获取的cookie为空请在本地使用自己cookie
	public function getejson($h5api,$r,$cookie){ 
		global $cookie;
		//参数处理
		$h5api=str_replace('jsonp','json',$h5api);
		if(stripos($h5api,'&sign') || stripos($h5api,'&t=')){
			$h5api=preg_replace('/&sign=\w+/','',$h5api);
			$h5api=preg_replace('/&t=\d+/','',$h5api);
		}
		if(stripos($h5api,'&data')){
			preg_match('/&data=(.*7D$)/',$h5api,$data);
			$h5api=preg_replace('/&data=.*7D$/','',$h5api);
			$data=$data[1];
		}
		if(empty($data)) exit("参数的data不存在或者错误");
		if(strpos($data,'%3A')) $data = urldecode($data);
		if(!empty($r)) $data=$this->editdata($r,$data);//条件查询
		$cookie = $this->getcookie();
		if($cookie==''){//读取本地cookie 在浏览器登陆淘宝后在console里输入document.cookie
			$cookie = "cookie.txt";
			if(file_exists($cookie)){
				$fp = fopen($cookie,"r")or die("文件不存在");
				$cookie = fread($fp,filesize($cookie));
				fclose($fp);
			}else{
				exit("目录下cookie.txt不存在或者为空，请更新cookie");
			}
		}
		if(empty($cookie)) exit("你的cookie已失效");		        
		$appKey= 12574478;                                 
		$_m_h5_tk= $this->get_word($cookie,'_m_h5_tk=', '_');//从cookie中取出_m_h5_tk，必须要去掉后面的部分
		$t =$this->getMillisecond();//生成时间戳   
		$url_data = urlencode($data);                                    
		$sign=md5($_m_h5_tk."&".$t."&".$appKey."&".$data); //生成sign
		$url = $h5api."&t=".$t."&sign=".$sign."&data=".$url_data;
		$d=$this->curl($url);//获取数据
		return $d;
        }
		
	//处理data数据 $r是一个数组
	public function editdata($r,$data){
		if(!is_array($r)) exit('检查你的参数是否是数组');
		foreach($r as $k=>$v){
			if(stripos($data,$k)){
				$data=preg_replace('/"'.$k.'":".*?"/','"'.$k.'":"'.$v.'"',$data);
					}
		}
		return $data;
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
			 $url="https://h5api.m.taobao.com/h5/mtop.user.getusersimple/1.0/?jsv=2.5.1&appKey=12574478&t=1560264165264&sign=11cb7c27c6a6970108e95bcf71c9cf6c&api=mtop.user.getUserSimple&v=1.0&ecode=1&sessionOption=AutoLoginOnly&jsonpIncPrefix=liblogin&type=jsonp&dataType=jsonp&callback=mtopjsonpliblogin1&data=%7B%7D";//请求地址，必须带上这个默认的appkey
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
			}
		 }
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
	 	 
}
