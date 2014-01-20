<?php
	class BingTranslation
	{
		public $clientID;
		public $clientSecret;
		
		public function __construct($cid, $secret)
		{
			$this->clientID = $cid;
			$this->clientSecret = $secret;
		}
			
		public function get_access_token()
		{	
			//if access token is not expired and is stored in COOKIE
			if(isset($_COOKIE['bing_access_token']))
				return $_COOKIE['bing_access_token'];
		
			// Get a 10-minute access token for Microsoft Translator API.
			$url = 'https://datamarket.accesscontrol.windows.net/v2/OAuth2-13';
			$postParams = 'grant_type=client_credentials&client_id='.urlencode($this->clientID).
			'&client_secret='.urlencode($this->clientSecret).'&scope=http://api.microsofttranslator.com';
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
			$rsp = curl_exec($ch); 
			$rsp = json_decode($rsp);
			$access_token = $rsp->access_token;
			
			setcookie('bing_access_token', $access_token, $rsp->expires_in);
			
			return $access_token;
		}
		
		public function translate($word, $from, $to)
		{
			$access_token = $this->get_access_token();
			$url = 'http://api.microsofttranslator.com/V2/Http.svc/Translate?text='.$word.'&from='.$from.'&to='.$to;
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:bearer '.$access_token));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
			$rsp = curl_exec($ch); 
			
			preg_match_all('/<string (.*?)>(.*?)<\/string>/s', $rsp, $matches);

			return $matches[2][0];
		}
		
		public function translate2($word, $from, $tos)
		{
			//translates 1 word to several languages
			//$tos is array of languages to translate to
			//returns array of translations as $result['en']=>'Hello'
			
			$access_token = $this->get_access_token();
			
			$result[$from] = $word;
			
			foreach($tos as $to)
			{
				$url = 'http://api.microsofttranslator.com/V2/Http.svc/Translate?text=hello&from='.$from.'&to='.$to;
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url); 
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:bearer '.$access_token));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
				$rsp = curl_exec($ch); 
				
				preg_match_all('/<string (.*?)>(.*?)<\/string>/s', $rsp, $matches);
	
				$result[$to] = $matches[2][0];
			}
			
			return $result;
		}
	}
	
	$bt = new BingTranslation($your_client_id, $your_secret);
	echo $bt->translate('Hello', 'en', 'ru');
?>
