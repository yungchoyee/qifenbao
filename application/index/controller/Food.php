<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Session;
use think\Cookie;;

class Food extends Controller
{
	public function info()
	{
		
		$food = trim(input('post.food'));
		//$food='瓜';
		$weight=trim(input('post.weight'));
		if(empty($food) || empty($weight))
		{
			$arr=['status'=>0,'msg'=>'没接到数据'];
		}
		else
		{
			//$weight=200;
			$res=Db::table('food')->where('name','like',"%{$food}%")->select();
			//var_dump($res);
			if($res)
			{
				$arr=['status'=>1,'food'=>$res[0]['name'],'cal'=>floatval($res[0]['cal']*$weight)];
			}
			else
			{
				$arr=['status'=>0,'msg'=>'对不起，没有！'];
			}
		}
		
		echo json_encode($arr);
	}

	      public function _empty()
    {
        echo "<script type='text/javascript' src='//qzonestyle.gtimg.cn/qzone/hybrid/app/404/search_children.js' charset='utf-8' homePageUrl='http://qifenbao.yungchoyee.top' homePageName='回到我的主页'></script>";
    }

}