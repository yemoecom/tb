<?php
define('ROOT_PATH',dirname(__FILE__));
require ROOT_PATH.'/init.php';
$mange=new Mange();


//h5api地址中去除t、sign、data参数
//data 参数url转后没转的都可以！没转的记得转后的双引号去掉
$h5api='https://h5api.m.taobao.com/h5/mtop.taobao.wsearch.appsearch/1.0/?jsv=2.5.1&appKey=12574478&api=mtop.taobao.wsearch.appSearch&v=1.0&H5Request=true&AntiCreep=true&type=jsonp&timeout=3000&dataType=jsonp&callback=mtopjsonp6';
$data='{"m":"shopitemsearch","vm":"nw","sversion":"4.6","shopId":"219465251","sellerId":"3074861492","style":"wf","page":"1","sort":"_coefp","catmap":"","wirelessShopCategoryList":""}';
$info=$mange->getejson($h5api,$data);

echo $info;






?>