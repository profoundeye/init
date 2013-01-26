<?php
/**
 * 乐做网新浪微博连接程序
 * @author anythink <xi@anythink.com.cn>
 * @version 1.0
 * @package lib/weibo
 */

class sinaConnect{

	public $error  = '';
	public $conn = '';


	function __construct(){
		$this->conn = spClass('openConnect');
		$this->conn->className = __CLASS__;
		require_once('saetv2.ex.class.php');
	}
	
	function init($appid,$appkey,$callback)
	{
		define("WB_AKEY", $appid);
		define("WB_SKEY", $appkey);
		define("WB_CALLBACK_URL", $callback);
	}

	/*初始化时候获得跳转地址*/
	function goLoginUrl(){		
		$this->clean();
		$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
		//$state = uniqid( 'weibo_', true);
		//$_SESSION['weibo_state'] = $state;
		$url =  $o->getAuthorizeURL(WB_CALLBACK_URL);
		header("Location:$url");
	}

	function callback(){
    	$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
		
		if(isset($_REQUEST['code'])) {
			$keys = array();

			/* 验证state，防止伪造请求跨站攻击
			$state = $_REQUEST['state'];
			if ( empty($state) || $state !== $_SESSION['weibo_state'] ) {
				$this->error = '非法请求！验证state失败';
				return false;
			}
			unset($_SESSION['weibo_state']);
			*/
			$keys['code'] = $_REQUEST['code'];
			$keys['redirect_uri'] = WB_CALLBACK_URL;
			try {
				$token = $o->getAccessToken( 'code', $keys ) ;
			} catch (OAuthException $e) {
			}
	
		}

		if ($token) {
			$_SESSION['weibo']['oauth_token'] = $token['access_token'];
			$_SESSION['weibo']['openid'] = $token['uid'];
			$_SESSION['weibo']['expires'] = time() + $token['expires_in'];
			$c = new SaeTClientV2( WB_AKEY , WB_SKEY ,$_SESSION['weibo']['oauth_token']);
			$info = $c->show_user_by_id($_SESSION['weibo']['openid']);//根据ID获取用户等基本信息
			if($info['gender'] == 'm'){
				$info['gender'] = 1;
			}else if($info['gender'] == 'f'){
				$info['gender'] = 2;
			}else{
				$info['gender'] = 3;
			}

			$userinfo = array(
				'name'   => $info['screen_name'],
				'location' => $info['location'],
				'desc'     => $info['description'],
				'avatar'   => $info['avatar_large'],
				'domain'   => $info['domain'],
				'sex'      => $info['gender'],
			);
			$_SESSION['weibo']['userinfo'] = $userinfo;
			$_SESSION['weibo']['type'] = 2;
			return true;
		}
		return false;
	}

	/**
	 * @param        $token
	 * @param        $openid
	 * @param        $text 发布文字
	 * @param string $img 是否图片是图片 如果是图片就传图片地址支持url and local path
	 * @return bool
	 */
	function postWeibo($token,$openid,$text,$img = ''){
		$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $token );
		if(!empty($img)){
			$ret = $c->upload($text,$img);
		}else{
			$ret = $c->update($text);//发送微博
		}
		if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
			$this->error =  $ret['error_code'].' - '.$ret['error'];
			return false;
		}
		return true;
	}

	function clean(){
		unset($_SESSION['userinfo']);
		unset($_SESSION['type']);
		unset($_SESSION['token']);
		unset($_SESSION['userinfo']);
	}

}


