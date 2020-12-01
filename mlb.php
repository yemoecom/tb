<?php
define('ROOT_PATH',dirname(__FILE__));
require ROOT_PATH.'/init.php';
//$mange=new Mange();
//$info=$mange->requery($url);
//$h5api='https://h5api.m.taobao.com/h5/mtop.taobao.wsearch.appsearch/1.0/?jsv=2.5.1&appKey=12574478&t=1605063873688&sign=1614fef44d5cacd241ea23d5534710db&api=mtop.taobao.wsearch.appSearch&v=1.0&H5Request=true&AntiCreep=true&type=jsonp&timeout=3000&dataType=jsonp&callback=mtopjsonp6&data=%7B%22m%22%3A%22shopitemsearch%22%2C%22vm%22%3A%22nw%22%2C%22sversion%22%3A%224.6%22%2C%22shopId%22%3A%22219465251%22%2C%22sellerId%22%3A%223074861492%22%2C%22style%22%3A%22wf%22%2C%22page%22%3A%221%22%2C%22sort%22%3A%22_coefp%22%2C%22catmap%22%3A%22%22%2C%22wirelessShopCategoryList%22%3A%22%22%7D';

//$r=array("tab"=>1);

// $info=$mange->getejson($h5api,$r);
// if(empty($info)) echo '不支持此接口！';
// echo '<pre>';
// print_r(json_decode($info,true));


//$arr=array("userid"=>'2457942829',"sort"=>'s',"page"=>'3');


$r=array('https://snzdy.tmall.com/','583016369093');
$title='桑蚕';
$list=new Lists();
//$info=$list->_getpl($r);
//$info=$list->_getshopid($r);
//$info=$list->_getshops($r);
//$info=$list->_getprodoctinfo($r);
//$info=$list->_gettotalpl($r);
//$info=$list->_getkwshops($r);
//$info=$list->_getcategory($r);
$info=$list->_getshopinfo($r);
//$info=$list->_getallshops($r);
//$info=$list->_shoptop($r);
//$info=$list->title_split($title);
//$info=$list->xialaci($title);

print_r($info);
