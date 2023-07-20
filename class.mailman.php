<?php
class PHPMailman {
		var $version = "Version 1.0";
		var $host = null;
		var $name = null;
		var $adminpw = null;
		var $token = null;

	function __construct($host,$name,$adminpw){
		$this->host=$host;
		$this->name=$name;
		$this->adminpw=$adminpw;
		$this->cookiefile=tempnam("","");
	} //constructor

	//inloggning--------------------------------------------------------------------
	function login(){
		$post_data = array();
		$post_data['adminpw'] = $this->adminpw;
		$url="https://".$this->host."/mailman/admin/".$this->name;

		$ret=$this->fetch($url,$post_data);
		list($ret_http_code,$ret_data)=explode("|",$ret);
		if ($ret_http_code != 200) {
		print 'Could not login. Error: ' . $ret_http_code;
		exit;
		}
	}

	function logout(){
		$url="https://".$this->host."/mailman/admin/".$this->name."/logout";

		$ret=$this->fetch($url);

		//remove cookiefile
		unlink($this->cookiefile);

		list($ret_http_code,$ret_data)=explode("|",$ret);
		if ($ret_http_code != 200) {
		print 'Could not logout. Error: ' . $ret_http_code;
		exit;
		}
	}


	//-------------------------------------------------------------------------------------------------
	function roster(){
		$i=0;
		$url="https://".$this->host."/mailman/roster/".$this->name;
		$ret=$this->fetch($url);
		list($ret_http_code,$ret_data)=explode("|",$ret);
		if ($ret_http_code != 200) {
		print 'Error while list data: ' . $ret_http_code;
		exit;
		}

		//echo "<pre>";
		$out=array();;
		$flag=0;
		foreach (explode("\n",$ret_data) as $line) {
				if (preg_match("/<ul>/",$line))
				{
					$flag=1;
				}
				if (preg_match("/<\/ul>/",$line))
				{
					$flag=0;
				}
				if ($flag == 1) {
					$line = str_replace(" at ","@",strip_tags($line));
					if (!empty($line)){
						$out[] = $line;
					}
					//if (strstr($line,"@")){
					//echo "Line: " . $line . "\n";
					//echo $out . "\n";
					//}
				}
		}	
		//echo "</pre>";
		return json_encode($out);
	}
	//-------------------------------------------------------------------------------------------------
	function subscribe($email, $notification=0){
		$post_data = array();
		$post_data['send_welcome_msg_to_this_batch'] = "0";
		$post_data['send_notifications_to_list_owner'] = "0";
		$post_data['subscribe_or_invite'] = "0"; // 0=subscribe
		$post_data['subscribees'] = is_array($email) ? implode("\n",$email):$email;
		$post_data['csrf_token'] = $this->token;
		$url="https://".$this->host."/mailman/admin/".$this->name."/members/add";

		$ret=$this->fetch($url,$post_data);
		list($ret_http_code,$ret_data)=explode("|",$ret);
		if ($ret_http_code != 200) {
		print 'Add. Error: ' . $ret_http_code;
		exit;
		}
	}	
	//-----------------------------------------------------------------------------------------------------
	function unsubscribe($email, $notification="0"){
		$post_data = array();
		$post_data['send_unsub_ack_to_this_batch'] = "0";
		$post_data['send_unsub_notifications_to_list_owner'] = "0";
		$post_data['unsubscribees'] = is_array($email) ? implode("\n",$email):$email;
		$post_data['csrf_token'] = $this->token;
		$url="https://".$this->host."/mailman/admin/".$this->name."/members/remove";

		$ret=$this->fetch($url,$post_data);
		list($ret_http_code,$ret_data)=explode("|",$ret);
		if ($ret_http_code != 200) {
		print 'Remove. Error: ' . $ret_http_code;
		exit;
		}
	}

	function fetch($url,$post_data=""){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1); // don't use a cached version of the url
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiefile);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiefile);
		if (!empty($post_data)){
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		}
		$data = curl_exec($ch);
		if (curl_errno($ch)) die(curl_error($ch));

		$pos = strpos($data,'csrf_token');
		if ($pos !== false) {
			$valuePos = strpos($data,'value=',$pos);
			if ($valuePos !== false) {
				//get text starting from the 'value=' portion of the string
				$data = substr($data,$valuePos);
				$arr = explode('"',$data);
				//value will be in $arr[1]
				//echo $arr[1];
				$this->token = $arr[1];
			}
		}
		
		$intReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $data;
		//print_r(debug_backtrace());
		curl_close($ch);
		unset($ch);
		unset($post_data);
		return $intReturnCode."|".$data;
	}
}
?> 
