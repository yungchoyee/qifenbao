<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Session;
use think\Cookie;

use app\index\model\User as UserModel;

class User extends Controller
{
	 //获取当前登录用户
    static public function user()
    {
      if(empty(session('')))
        {
          $arr=['info'=>['islogin'=>false]];
        }
        else
        {
          $arr=session('');
        }
      
      return $arr;
    }
    //用户密码注册方法 
    public function register()
    {	
        
       $data['username'] = trim(input('post.username'));
        $data['password'] = md5(input('post.password'));
  
        //查询数据库是否有该用户
        $res = Db::name('user')->where(['username'=>$data['username']])->select();
        if($res){
            $arr = ['status'=>0,'msg'=>'用户名已存在'];
        }else{
         $result = Db::name('user')->insert($data);
            if($result){
                 $arr = ['status'=>1,'msg'=>'注册成功'];
                  // $info = ['username'=>$data['username'],'password'=>$data['password']];
                  // session('info',$info);
            }else{
                $arr = ['status'=>0,'msg'=>'注册失败'];
            }
        }
        echo json_encode($arr);
    }
  

   //登录
   public function login()
    {   
        
       $username = trim(input('post.username'));
       $password = md5(input('post.password'));
       
       $autologin = input('post.autologin');
      
        //模拟数据
        // $username = '你好吗';
        // $password = md5(111111);
        // $autologin = false;


         $res = Db::table('user')->where("username = '$username' && password = '$password'")->select();
            if($res){
                //登录成功,
                $uid = $res[0]['uid'];
                $username = $res[0]['username'];
                $pic = Db::table('user')->where("uid = $uid")->select();
                $pic = $pic[0]['pic'];
                $info = ['title'=>$username,'imgUrl'=>$pic,'id'=>$uid,'islogin'=>true];
                session('info',$info);
                //判断是否记住密码
                if($autologin == 'true'){
                    cookie('username', input('post.username'),3600*24);
                    cookie('password', md5(input('post.password')),3600*24);
                }else{
                  cookie('username', null);
                  cookie('password', null);
                }
               
                $arr = ['status'=>1,'username'=>$res[0]['username'],'pic'=>$res[0]['pic'],'isLogin'=>true,'msg'=>'登录成功'];
            }else{
                //登录失败
                 $arr = ['status'=>2,'msg'=>'账号或密码错误'];
            }
        //}
        echo json_encode($arr);

   }

    public function logout()
    {
        // $_SESSION = null;
        session(null);
       if(empty(session(''))){
          $arr = ['status'=>1,'msg'=>'退出成功'];
       }else{
          $arr = ['status'=>0,'msg'=>'退出失败'];
       }
       echo json_encode($arr);
    }

     //用手机短信登录
   public function tellogin()
   {
      $tel = input('post.tel');
      $res = Db::table('user')->where("username = '$tel'")->select();
      // var_dump($res);die;
      if(!$res){
         //用这个手机号码注册一个新账号
         $data['username'] = $tel;
         $lastId = Db::name('user')->insertGetId($data);
         // echo $lastId;die;
         if($lastId){
            $info['uid'] = $lastId;
            session('info',$info);

            $arr = ['status'=>1,'isLogin'=>true,'msg'=>'登录成功,您下次可以继续使用本手机号码登录'];
            echo json_encode($arr);
         }else{
             $arr = ['status'=>0,'isLogin'=>false,'msg'=>'账号未注册'];
            echo json_encode($arr);
         }
      }else{
          $info['uid'] = $res[0]['uid'];
          session('info',$info);
           $userinfo = [
                        ];
                $arr = ['status'=>1,'username'=>$res[0]['username'],
                             'pic'=>$res[0]['pic'],'isLogin'=>true,'msg'=>'登录成功'];
      }
      echo json_encode($arr);
   }

     //ajax检查短信或邮件验证码是否正确
    public function checkmsg(){

        $captcha = strtolower(input('get.code'));
        $check = $_SESSION['checkmobile'];

        if($check !== $captcha){
         $arr = ['status'=>0,'msg'=>'验证码输入错误'];
        }else{
         $arr = ['status'=>1,'msg'=>'验证通过'];
        }
        echo json_encode($arr);
    }


    //调用短信接口
    public function getCode()
    { 

        return  include('vendor/framework/lx/src/message.php');

    }

      //用户签到
    public function dosign()
    { 

        if(!session('info')['id']){
            $arr = ['state'=>false,'error'=>'请先登录'];
        }else{
        	
           $uid =  session('info')['id'];
           //查询上次签到时间
           $userinfo = Db::table('user')->where('uid',$uid)->find();

           //判断签到次数
           if($userinfo['sign'] == 6){
              $arr = ['state'=>false,'error'=>'您已经签到6天了'];
              echo json_encode($arr);
              die;
           }
         
           $lastsign = $userinfo['lastsign'];
           //判断上次签到时间与现在时间相差多久
           $distance = time() - $lastsign;
           // var_dump($distance);die;
           $allowSign = (($distance > 10) ? true : false);
           // var_dump($allowSign);die;
           // $allowSign = true;
           //如果超过24小时让他签到
          // echo 22;die;
           if(!$allowSign){
    	// echo  2222;die;
            $arr = ['state'=>false,'msg'=>'距离上次签到不足1天'];
              // var_dump($arr);die;
           }else{
              $time = time();
              //如果是第一次签到，记录第一次签到的时间
              if($userinfo['firstsign'] !== 0){
                  $res =  Db::query("update user set lastsign = $time,sign = sign + 1 where uid= $uid");
                  $distance = $time - $userinfo['firstsign'];
                  // var_dump($distance);die;
                  $fields = $this->checkday($distance);
                 $title = $this->jitian($fields);
                  Db::query("update sign set $fields=1 where user_id =$uid");
                   $arr = ['state'=>true,'msg'=>'签到成功','title'=>$title,'imgUrl'=>'http://www.blackmed.cn/static/imgs/singin.png'];
              }else{
                  $res =  Db::query("update user set lastsign = $time,sign = sign + 1,firstsign = $time where uid= $uid");
                  Db::name('sign')->insert(['user_id'=>$uid,'one'=>1]);
                  $fields = 'one';
                  $title = $this->jitian($fields);
                
                  $arr = ['state'=>true,'msg'=>'签到成功','title'=>$title,'imgUrl'=>'http://www.blackmed.cn/static/imgs/singin.png'];

              }
              // var_dump($fields);die;
             
           }
          
        }

        echo json_encode($arr);
       
    }

    //判断是签到第几天
    public  function  checkday($distance)
    {   

        if($distance > 5*10){
            $fields = 'six';
        }else if($distance > 4*10){
            $fields = 'five';
        }else if($distance > 3*10){
            $fields = 'four';
        }else if($distance > 2*10){
            $fields = 'three';
        }else if($distance > 1*10){
            $fields = 'two';
        }

        return $fields;
    }

    public function signday()
    {	
          if(!session('info')['id']){
            $arr = ['state'=>false,'error'=>'请先登录'];
            echo  json_encode($arr);
          }else{
              $uid =  session('info')['id']; 

              $res = Db::name('sign')->where('user_id',$uid)->find();

              if(!$res){
                  $arr = [
                      ['title'=>'第1天','state'=>0,'imgUrl'=>'http://qifenbao.yungchoyee.top/static/imgs/singin1.png'],
                       ['title'=>'第2天','state'=>0,'imgUrl'=>'http://qifenbao.yungchoyee.top/static/imgs/singin1.png'],
                        ['title'=>'第3天','state'=>0,'imgUrl'=>'http://qifenbao.yungchoyee.top/static/imgs/singin1.png'],
                         ['title'=>'第4天','state'=>0,'imgUrl'=>'http://qifenbao.yungchoyee.top/static/imgs/singin1.png'],
                          ['title'=>'第5天','state'=>0,'imgUrl'=>'http://qifenbao.yungchoyee.top/static/imgs/singin1.png'],
                           ['title'=>'第6天','state'=>0,'imgUrl'=>'http://qifenbao.yungchoyee.top/static/imgs/singin1.png']

                  ];
                  echo json_encode($arr);

              }else{

                $res = Db::table('sign')->field('one,two,three,four,five,six')->where('user_id',$uid)->select()[0];
         
                foreach ($res  as $key => $val){
                  $arr[] = ['title'=>$this->jitian($key),'state'=>($val == 1 ? true : false),'imgUrl'=>($val == 1 ? 'http://qifenbao.yungchoyee.top/static/imgs/singin.png' : 'http://qifenbao.yungchoyee.top/static/imgs/singin1.png')];
                }
               echo json_encode($arr);
              }
              
          }
     
    }

    public function jitian($fields)
    { 
    
          switch($fields){
          case 'one' :
              $title = '第1天';
              break;
          case 'two' :
              $title = '第2天';
              break;
          case 'three' :
              $title = '第3天';
              break;
          case 'four' :
              $title = '第4天';
              break;
          case 'five' :
              $title = '第5天';
              break;
          case 'six' :
              $title = '第6天';
              break;

      } 

        return $title;
    }

    public function _empty()
    {
    	echo "<script type='text/javascript' src='//qzonestyle.gtimg.cn/qzone/hybrid/app/404/search_children.js' charset='utf-8' homePageUrl='http://qifenbao.yungchoyee.top' homePageName='回到我的主页'></script>";
    }
}

