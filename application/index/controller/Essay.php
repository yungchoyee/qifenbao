<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Essay extends Controller
{
	//查看收藏
	public function shoucang()
	{
		if(empty(session('')))
		{
			$arr['status']=0;
			$arr['msg']='去登录';
			$arr['essay']=null;
		}
		else
		{
			$uid=session('info')['id'];
			//$uid=14;
			$res = Db::name('collect')->field('eid')->where(['uid'=>$uid])->order('id','desc')->limit(10)->select();
			$eids=[];
			//如果有结果，收藏的存进数组
			if($res)
			{
				foreach ($res as $key => $value) {
					$eids[]=$value['eid'];
				}
				//按顺序查列表详情
				$essay=[];
				for ($i=0; $i < count($eids); $i++) { 
					$a=Db::table('essay')->field('eid,title,auth,addtime,pic')->where('eid',$eids[$i])->select();
					$essay[]=$a[0];
				}
				//转换时间
				foreach ($essay as $key=>$value) {
					$essay[$key]['addtime']=date('Y-m-d H:i:s',$value['addtime']);
				}
				$arr['status']=1;
				$arr['msg']='有收藏';
				$arr['essay']=$essay;
			}
			else
			{
				$arr['status']=0;
				$arr['msg']='你啥也没收藏';
				$arr['essay']=null;
			}
		}
		
		//var_dump($arr);die;
		echo json_encode($arr);
		
	}
	//加入收藏
	public function collect()
	{
		if(!empty(session('')))
		{
			$uid=session('info')['id'];
			//$uid=16;
			//
			$eid=input('post.eid');
			//$eid=80;
			$res = Db::name('collect')->where(['uid'=>$uid,'eid'=>$eid])->select();
			if(!$res)
			{
				$data=['uid'=>$uid,'eid'=>$eid];
				$docollect=Db::name('collect')->insert($data);
				//var_dump($docollect);
				if($docollect)
				{
					$arr['msg']='收藏成功';
					$arr['status']=1;
				}
				else
				{
					$arr['msg']='收藏失败';
					$arr['status']=0;
				}
			}
			else
			{
				$arr['msg']='收藏过了';
				$arr['status']=0;
			}
		}
		else
		{
			$arr['msg']='去登录去登录';
			$arr['status']=0;
		}
		echo json_encode($arr);
		//var_dump($arr);
	}
	//搜索
	public function essay_search()
	{
		//echo '111';die;
		$keyword=trim(input('post.keyword'));
		//$keyword='葱';
		if($keyword)
		{
			$res=Db::table('essay')->field('eid,title,auth,addtime,pic')->where('title','like',"%{$keyword}%")->select();
			if($res)
			{
				foreach ($res as $key=>$value) {
					$res[$key]['addtime']=date('Y-m-d H:i:s',$value['addtime']);
				}
			 	$arr['essay']=$res;
			}
			else
			{
			 	$arr['msg']='不好意思，没有！';
			}
		}
		else
		{
			$arr['msg']='你快输入呀';
		}
		
		//$arr['msg']='不！';
		echo json_encode($arr);
	}
	//最近阅读
	public function near_read()
	{
		$uid=session('info')['id'];
		//$uid=14;
		//查询近期看的十篇文章
		$res = Db::name('read')->field('eid')->where(['uid'=>$uid])->order('id','desc')->limit(10)->select();
		//var_dump($res);

		//存成数组
		$eids=[];
		if($res)
		{
			foreach ($res as $key => $value) {
				$eids[]=$value['eid'];
			}
		}
		else
		{
			$arr['msg']='你啥也没读过';
		}
		//文章列表详情
		$essay=[];
		for ($i=0; $i < count($eids); $i++) { 
			$a=Db::table('essay')->field('eid,title,auth,addtime,pic')->where('eid',$eids[$i])->select();
			$essay[]=$a[0];
		}
		foreach ($essay as $key=>$value) {
			$essay[$key]['addtime']=date('Y-m-d H:i:s',$value['addtime']);
		}
		
		 $arr['essay']=$essay;
		 //var_dump($arr);die;
		echo json_encode($arr);
		
	}
	//加入最近阅读
	public function read($eid)
	{
		$uid=session('info')['id'];
		$res = Db::name('read')->where(['uid'=>$uid,'eid'=>$eid])->select();
		if(!$res)
		{
			$data=['uid'=>$uid,'eid'=>$eid];
			Db::name('read')->insert($data);
		}
	}
	//判断当前是否加入收藏了
	public function is_collect($eid)
	{
		$uid=session('info')['id'];
		//$eid=81;
		$res = Db::name('collect')->where(['uid'=>$uid,'eid'=>$eid])->select();
		if($res)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
	//详情
	public function info_byeid()
	{
		$eid=input('post.eid');
		//$eid=80;
		//登录了
		if(!empty(session('')))
		{
		//加入最近阅读
			$this->read($eid);
			//判断是否被收藏
			$collect=$this->is_collect($eid);
			$arr['status']=$collect;
		}
		//没登录
		else
		{
			$arr['status']=0;
		}
		$res=Db::table('essay')->field('content')->where('eid',$eid)->select();
		//var_dump($res[0]['content']);
		$arr['content']=$res[0]['content'];
		echo json_encode($arr);
	}
	//列表
	public function lists()
	{
		//$id=input('post.id');
		//sleep(10);
		$id=input('post.id');
		//$id=1;
		switch ($id) {
			case 1:
				$this->all_list();
				break;
			case 2:
				$this->essay_list('禁忌');
				break;
			case 3:
				$this->essay_list('健脑');
				break;
			case 4:
				$this->essay_list('防癌');
				break;
			case 5:
				$this->essay_list('助睡眠');
				break;
			case 6:
				$this->essay_list('补血养肾');
				break;
			case 7:
				$this->essay_list('养胃');
				break;
			case 8:
				$this->essay_list('美容减肥');
				break;
			case 9:
				$this->essay_list('降');
				break;
			case 10:
				$this->essay_list('防霾');
				break;
			case 11:
				$this->essay_list('免疫');
				break;
			default:
				$a['mess']=$id.'没有';
				echo json_encode($a);
				break;
		}
	}
	//列表
	public function essay_list($type)
	{
		$res=Db::table('essay')->field('eid,title,auth,addtime,pic')->where('type',$type)->select();
		foreach ($res as $key=>$value) {
			$res[$key]['addtime']=date('Y-m-d H:i:s',$value['addtime']);
			
		}
		//var_dump($res);
		$arr['essay']=$res;
		 //var_dump($arr);die;
		echo json_encode($arr);
	}
	//所有的
	public function all_list()
	{
		$res=Db::table('essay')->field('eid,title,auth,addtime,pic')->select();
		foreach ($res as $key=>$value) {
			$res[$key]['addtime']=date('Y-m-d H:i:s',$value['addtime']);
		}
		$arr['essay']=$res;
		echo json_encode($arr);
	}


	      public function _empty()
    {
        echo "<script type='text/javascript' src='//qzonestyle.gtimg.cn/qzone/hybrid/app/404/search_children.js' charset='utf-8' homePageUrl='http://qifenbao.yungchoyee.top' homePageName='回到我的主页'></script>";
    }

}