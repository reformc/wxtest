<?php
	/**
     * 控客智能硬件接口             
     * @author [reform] <[reformc@163.com]>
     * @version [1.0]   
     */
//echo '<meta charset="UTF-8">';

include("token.php");
$obj_kapi = new kapi;
$obj_kapi->parameter_sign($access_token,$userid);//参数从token.php获取，请自行缓存。
//echo $n = $obj_kapi->get_userid();//获取userid
//echo $n = $obj_kapi->get_k_list();//获取小K列表
//echo $n = $obj_kapi->get_device_state();//获取小K状态
//echo $n = $obj_kapi->set_k("open");//开关小K
//echo $n = $obj_kapi->get_remote_list();//获取普通遥控器列表
//echo $n = $obj_kapi->remote_order("ok");//发送普通遥控器命令
//echo $n = $obj_kapi->remote_conditioner_list();//获取空调遥控器列表
//echo $n = $obj_kapi->remote_conditioner_order("1.2.1.20");//发送空调遥控器命令//(,a 表示开关(0=关,1=开),b 表示 模式(0=自动,1=制冷,2=制热,3=除湿,4=送风),c 表示风速(0=自动,1= 低风,2=中风,3=高风),d 表示温度 )

class kapi{
	public $access_token;
	public $userid;//调试用
	function getstr(){//
		if(isset($_GET['set_k']) and ($_GET['set_k'] == "open" or $_GET['set_k'] == "close")){
			$n = $this->set_k($_GET['set_k']);
		}else if(isset($_GET['get_device_state']) and $_GET['get_device_state'] == 1){
			$n = $this->get_device_state();
		}
		return $n;
	}
	function parameter_sign($access_token,$userid){
		$this->access_token = $access_token;
		$this->userid = $userid;
	}
	function get_userid(){//获取userid
		$url = "http://kk.bigk2.com:8080/KOAuthDemeter/User/queryUserId ";
		$post_data = '{"username":"reformc@163.com"}';
		$result  = $this->poststr($url,$post_data);
		return json_decode($result) -> userid;
	}
	function get_k_list(){//获取小K列表
		$url = "http://kk.bigk2.com:8080/KOAuthDemeter/User/getKList";
		$post_data = '{"userid":"'.$this->userid.'"}';
		$result  = $this->poststr($url,$post_data);
		$array_n = json_decode($result,true);
		$n = "";
		$de_type = array("1"=>"1代","2"=>"2代","3"=>"mini","4"=>"miniPro");
		for($i=0;$i<count($array_n['datalist']);$i++){
			$n = $n."<br>".$array_n['datalist'][$i]['device_name']."/".$de_type[$array_n['datalist'][$i]['device_type']]."/".$array_n['datalist'][$i]['device_mac']."/".$array_n['datalist'][$i]['kid'];
		}
		//return $result;
		return $n;
	}
	function get_device_state(){//获取小K状态
		/*
		k2pro1/2代/28-d9-8a-07-5a-b2/b048ad0c-5d8b-4c62-a319-5739d79a9de9
		k2pro2/2代/28-d9-8a-07-50-39/e9bd24cf-2c1e-41e4-b0b6-0fe2b4072bf0
		办事处/miniPro/28-d9-8a-83-11-6d/5a8028ac-a3e2-4db3-83f1-594dfd0d2c61
		*/
		$url = "http://kk.bigk2.com:8080/KOAuthDemeter/KInfo/getKState";
		$post_data = '{"userid":"'.$this->userid.'","kid":"5a8028ac-a3e2-4db3-83f1-594dfd0d2c61"}';
		$result  = $this->poststr($url,$post_data);
		return json_decode($result)->data;
	}
	function set_k($key){//控制插座开关
		$url = "http://kk.bigk2.com:8080/KOAuthDemeter/KControl/doSwitchK";
		$post_data = '{"userid":"'.$this->userid.'","kid":"5a8028ac-a3e2-4db3-83f1-594dfd0d2c61","key":"'.$key.'"}';
		$result = $this->poststr($url,$post_data);
		if(json_decode($result)->des == 0){
			return json_decode($result)->des;}
		else{
			return $result;}
	}
	function get_remote_list(){//获取普通遥控器列表
		$url = "http://kk.bigk2.com:8080/KOAuthDemeter/User/getGeneralRemoteList";
		$post_data = '{"userid":"'.$this->userid.'"}';
		$result = $this->poststr($url,$post_data);
		return $result;
	}
	function remote_order($order_name){//发送普通遥控器命令
		$url = "http://kk.bigk2.com:8080/KOAuthDemeter/KControl/sendGeneralRemoteOrder";
		$kid = "5a8028ac-a3e2-4db3-83f1-594dfd0d2c61";
		$order = array("right"=>"tv_1512202186#1512202212","left"=>"tv_1512202186#1512202207","down"=>"tv_1512202186#1512202199","up"=>"tv_1512202186#1512202190","voiceup"=>"tv_1512202186#1512202218","voicedown"=>"tv_1512202186#1512202224","back"=>"tv_1512202186#1512202231","ok"=>"tv_1512202186#1512202252");
		$post_data = '{"userid":"'.$this->userid.'","kid":"'.$kid.'","remoteType":"1","order":"'.$order[$order_name].'"}';
		$result = $this->poststr($url,$post_data);
		return $result;
		//return json_decode($result)->des;
	}
	function remote_conditioner_list(){//获取空调遥控器列表
		$url = "http://kk.bigk2.com:8080/KOAuthDemeter/User/getAirConditionerRemoteList";
		$post_data = '{"userid":"'.$this->userid.'"}';
		$result = $this->poststr($url,$post_data);
		return $result;
	}
	function remote_conditioner_order($extraOrder){//发送空调遥控器命令
		$url = "http://kk.bigk2.com:8080/KOAuthDemeter/KControl/sendAirConditionerOrder";
		$kid = "5a8028ac-a3e2-4db3-83f1-594dfd0d2c61";
		//$extraOrder = "1.2.1.20";//(0=自动,1=制冷,2=制热,3=除湿,4=送风),c 表示风速(0=自动,1= 低风,2=中风,3=高风)
		$post_data = '{"userid":"'.$this->userid.'","kid":"'.$kid.'","remoteType":"1","baseOrder":"GREE&YB0F2","extraOrder":"'.$extraOrder.'"}';
		$result = $this->poststr($url,$post_data);
		return $result;
	}
	function poststr($url, $post_data) {//post方法
			$options = array(  
				'http' => array(  
					'method' => 'POST',  
					'header' => "Content-type:application/json\r\n"."Authorization:Bearer ".$this->access_token,  
					'content' => $post_data,  
					'timeout' => 15 * 60
				)  
			);
			$context = stream_context_create($options);  
			$result = file_get_contents($url, false, $context);
			//return json_decode($result) -> userid;  
			return $result;
	}

}
?>