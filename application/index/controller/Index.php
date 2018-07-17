<?php
namespace app\index\controller;
use think\Db;
use think\Controller;
use think\View;
use app\index\controller\User as UserController;
class Index
{
    public function index()
    {
        $View = new View();
        
        return $View->fetch();
    }
    //主页，首页
    public function main()
    {
        $silder=Db::table('slider')->select();
        //var_dump($silder);
        $lists=Db::table('lists')->select();
        //var_dump($lists);
        $footimgs=Db::table('footimgs')->select();
        //var_dump($footimgs);die;
        $arr=['slider'=>$silder,'lists'=>$lists,'footimgs'=>$footimgs];
        echo json_encode($arr);
    }

    //个人
     public function myinfo()
    {
        $person=UserController::user();

        $myinfo=Db::table('myinfo')->select();
        //var_dump($myinfo);
        $my=Db::table('my')->select();
        //var_dump($my);
        $arr=['personage'=>$person['info'],'message'=>$myinfo,'lately'=>$my];
        echo json_encode($arr);
    }

     public function _empty()
    {
        echo "<script type='text/javascript' src='//qzonestyle.gtimg.cn/qzone/hybrid/app/404/search_children.js' charset='utf-8' homePageUrl='http://www.blackmed.cn' homePageName='回到我的主页'></script>";
    }
}