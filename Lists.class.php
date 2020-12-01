<?php
class Lists extends Mange{
	public $arr=array();
	/*
	获取评论
	数组$arr
	$itemid 商品ID
	page 页数
	
	*/
	public function _set_arr($arr){
		if(!is_array($arr)) exit('你的数组参数错误');
		$arr=parent::arr_replace($arr);
		if(empty($arr['page'])) $arr['page']=1;
		return $arr;
	}
	public function _getpl($arr){
		$arr=$this->_set_arr($arr);
		$url='https://rate.tmall.com/list_detail_rate.htm?itemId='.$arr['itemid'].'&sellerId=174383248011&order=3&currentPage='.$arr['page'];
		return parent::requery($url);
	}
	//总评价
	public function _gettotalpl($arr){
		$arr=$this->_set_arr($arr);
		$url='https://rate.tmall.com/listTagClouds.htm?itemId='.$arr['itemid'];
		return parent::curls($url);
	}
	
	/**
	获取店铺id号方法
	url为天猫地址
	**/
	public function _getshopid($arr){ 
		$arr=$this->_set_arr($arr);
		return parent::getshop($arr['url']);
	}
	
	/*
	通过产品ID获取店铺信息
	*/
	public function _getshopinfo($arr){
		$info=$this->_getprodoctinfo($arr);
		return parent::setshopinfo($info);
	}
	
	
	/*
	获取店铺所有产品
	url 店铺地址
	page 页数
	*/
	public function _getshops($arr){
		$arr=$this->_getshopid($arr);
		$url='https://scud.m.tmall.com/shop/shop_auction_search.do?spm=a2141.7631565.0.0.95796db2R7ZQWU&suid='.$arr['userId'].'&sort=s&p='.$arr['page'].'&page_size=12&from=h5&shop_id='.$arr['shopId'].'&ajson=1&_tm_source=tmallsearch';
		return parent::curls($url);
	}
	
	/*
	通过产品ID获取产品的详细信息
	$itemid 产品ID号
	//https://nswex.com/index.php?route=product/daigou/json&search=url 备用地址
	*/
	public function _getprodoctinfo($arr){
		$arr=$this->_set_arr($arr);
		$url='https://h5api.m.taobao.com/h5/mtop.taobao.detail.getdetail/6.0/?jsv=2.4.5&appKey=12574478&api=mtop.taobao.detail.getdetail&v=6.0&ttid=2016%40taobao_h5_2.0.0&isSec=0&ecode=0&AntiFlood=true&AntiCreep=true&H5Request=true&data=%7B%22exParams%22%3A%22%7B%5C%22id%5C%22%3A%5C%22'.$arr['itemid'].'%5C%22%7D%22%2C%22itemNumId%22%3A%22'.$arr['itemid'].'%22%7D';
		if(strpos(parent::getejson($url),'login.m.taobao.com')) {
			$url='https://asiagoodbuy.com/index.php?route=product/daigou/json&search=https%3A%2F%2Fdetail.tmall.com%2Fitem.htm%3Fid%3D'.$arr['itemid'];
			$detail=Mange::decodeUnicode(parent::curls($url));
		}else{
			$detail=parent::getejson($url);
		}
		$getdesc='https://h5api.m.taobao.com/h5/mtop.taobao.detail.getdesc/6.0/?jsv=2.4.11&appKey=12574478&t=1581137850&sign=123&api=mtop.taobao.detail.getdesc&v=6.0&type=jsonp&dataType=jsonp&timeout=20000&callback=mtopjsonp1&data=%7B%22id%22%3A%22'.$arr['itemid'].'%22%2C%22type%22%3A%220%22%2C%22f%22%3A%22TB1456%22%7D';
		$detail_2=parent::getejson($getdesc);
		
		return json_encode(
			array_merge_recursive(
					json_decode($detail,true),
					json_decode($detail_2,true)
				),JSON_UNESCAPED_UNICODE
		);
	}
	
	
	/*
	掌柜热卖
	kw  关键字
	count 显示的数量
	page 分页是count的倍数
	*/
	public function tmatch($arr){
		$kw=parent::parseurl($arr['kw']);
		$url='https://tmatch.simba.taobao.com/?name=tbuad&o=j&count='.$arr['count'].'&pid=430409_1006&keyword='.$kw.'&offset='.$arr['page'];
		return parent::requery($url);
	}
	
	
	/*
	获取店铺下的类目和类目下的产品
	shopId  店铺id
	sellerId 买家ID 
	*/
	public function _getcategory($arr){
		$arr=$this->_getshopid($arr);
		$url='https://h5api.m.taobao.com/h5/mtop.taobao.shop.wireless.category.get/1.0/?jsv=2.5.1&appKey=12574478&t=1605062018138&sign=fb9b22e5d7456760fbf2e39c6a926b1b&api=mtop.taobao.shop.wireless.category.get&v=1.0&H5Request=true&type=jsonp&dataType=jsonp&callback=mtopjsonp2&data=%7B%22shopId%22%3A%22'.$arr['shopId'].'%22%2C%22sellerId%22%3A%22'.$arr['userId'].'%22%7D';
		return parent::getejson($url);
	}
	
	/*
	通过关键字搜索产品的信息 产品有tag标签
	kw 输入的关键字
	page 分页 第一页是0第二页是44第三页是88！每一页是44相加
	*/
	public function _getkwshops($arr){
		$arr=$this->_set_arr($arr);
		$page=(int)($arr['page']) - 1;
		$page = $page * 44;	
		$kw=rawurlencode($arr['kw']);
		$ms=Mange::getMsec();//毫秒时间戳
		$nyr=Mange::nyr();//年月日
		$py=parent::pinyin($arr['kw'],1);
		$url='https://s.taobao.com/search?data-key=s&data-value='.$page.'&ajax=true&_ksTS='.$ms.'_753&callback=jsonp754&ie=utf8&spm=a21bo.2017.201856-taobao-item.2&sourceId=tb.index&search_type=item&ssid=s5-e&commend=all&imgfile=&q='.$kw.'&suggest=0_7&_input_charset=utf-8&wq='.$py.'&suggest_query='.$py.'&source=suggest&p4ppushleft=1%2C48';
		return Mange::decodeUnicode(parent::requerys($url));
	}
	
	/*
	获取店铺的所有产品  只能获取天猫店                  
	url 必须为天猫的url地址
	page 分页
	*/
	public function _getallshops($arr){
		$arr=$this->_set_arr($arr);
		$shop=$this->_getshopid($arr['url']); 
		preg_match('/[http|https]:\/\/(.*?)\.tmall\.com.*/',$arr['url'],$domain);
		//$shopId=$shop['shopId'];
		$url='https://'.$domain[1].'.m.tmall.com/shop/shop_auction_search.do?sort=s&p='.$arr['page'].'&page_size=12&from=h5&shop_id='.$shop['shopId'].'&ajson=1&_tm_source=tmallsearch';
		return parent::requerys($url);
	}
	
	//获取店铺前10的产品排名
	/*
	sort:  _sale 销量  _coefp综合  first_new新品  bid升序  _bid降序
	数组中必须是sort=>bid格式
	*/
	public function _shoptop($arr){
		$arr=$this->_set_arr($arr);
		$shop=$this->_getshopid($arr['url']);
		$url='https://h5api.m.taobao.com/h5/mtop.taobao.wsearch.appsearch/1.0/?jsv=2.5.1&appKey=12574478&t=1605063873688&sign=1614fef44d5cacd241ea23d5534710db&api=mtop.taobao.wsearch.appSearch&v=1.0&H5Request=true&AntiCreep=true&type=jsonp&timeout=3000&dataType=jsonp&callback=mtopjsonp6&data=%7B%22m%22%3A%22shopitemsearch%22%2C%22vm%22%3A%22nw%22%2C%22sversion%22%3A%224.6%22%2C%22shopId%22%3A%22'.$shop['shopId'].'%22%2C%22sellerId%22%3A%22'.$shop['userId'].'%22%2C%22style%22%3A%22wf%22%2C%22page%22%3A%221%22%2C%22sort%22%3A%22_sale%22%2C%22catmap%22%3A%22%22%2C%22wirelessShopCategoryList%22%3A%22%22%7D';
		return parent::getejson($url,$arr);
	}
	
	
	//标题分词
	public function title_split($title){
		return file_get_contents('http://api.91laihama.com/nlpir/split?title='.$title.'&key=f43b82042eba5ab7a3b203f2d1935782');
	}
	/*
	下拉词
	*/
	public function xialaci($kw){
		return file_get_contents('https://suggest.taobao.com/sug?code=utf-8&q='.$kw.'&_ksTS=1545615822749_1198&k=1&area=c2c&bucketid=11');
	}
	/*
	淘宝关键字搜索里找到的接口！根据关键字搜索店铺和店铺的产品列表
	kw 关键字
	page 分页
	*/
	public function tblm($arr){
		$arr=$this->_set_arr($arr);
		$arr['page']=$arr['page'] * 20;
		$url='https://tmatch.simba.taobao.com/?name=tbuad&o=j&count=20&p4p=tbcc_p4p_c2015_8_130026_16047286110521604728611130&pid=430409_1006&keyword='.parent::parseurl($arr['kw']).'&offset='.$arr['page'];
		return parent::requiresss($url);
	}
	
	//天猫关键字搜索
	// public function _gettmall_info($kw){
		// $kw=parent::parseurl($kw);
		
		// $url = 'https://list.tmall.com/m/search_items.htm?page_size=20&page_no=1&q='.$kw.'&type=p&tmhkh5=&spm=a220m.8599659.a2227oh.d100&from=mallfp..m_1_suggest&searchType=default&closedKey=';
		// return parent::requery($url);
	// }
	
	//
}
