<?php
class CrossDomainRequest extends DataObject {
	
	private static $db = array(
		'From' 		=> 'Varchar(255)',	// bring session from domain
		'To' 		=> 'Varchar(255)',	// bring session to domain
		'IP' 		=> 'Varchar(32)',	// bring session to domain
		'Hash' 		=> 'Varchar(32)',	// hash ID for 'gocrossdomain' GET value
		'Data' 		=> 'Text',			// serialized Session data
		'Finished' 	=> 'Boolean'
	);
	
	private static $has_one = array (
		'Member'	=> 'Member'
	);
	
	
	static function RequestSessionCrossDomainURL($from, $to, $URI = null){
		
		$hash = self::GenerateUniqueHash();
		
		$sessionData = Session::get_all();
		
		$fromDomain = self::GetDomainFromURL($from);	// return domain name if $from = 'http://test.com' 
		$toDomain 	= self::GetDomainFromURL($to);		// return false if $from = 'test.com' 
		
		$fromDomain = $fromDomain ? $fromDomain : $from;
		$toDomain 	= $toDomain ? $toDomain : $to;
		
		//save request
		$DO = new CrossDomainRequest();
		$DO->From 		= $fromDomain;
		$DO->To 		= $toDomain;
		$DO->IP 		= isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
		$DO->Hash 		= $hash;
		$DO->Data 		= serialize($sessionData);
		$DO->Finished 	= false;
		$DO->MemberID 	= Member::currentUserID();
		$DO->write();
		
		if($URI === null){
			$URI = $_SERVER['REQUEST_URI'];
		}
		
		return Controller::join_links($to, $_SERVER['REQUEST_URI'], '?gocrossdomain=' . $hash);
	}
	
	static function GenerateUniqueHash(){
		
		$isunique = false;
		
		$hash = '';
		
		while (1){
			$hash = md5(uniqid(mt_rand(), true));
			
			$DO = CrossDomainRequest::get()->filter(array('Hash' => $hash, 'Finished' => false))->first();
			
			if( ! ($DO && $DO->ID)){
				break;
			}
		}
		
		return $hash;
	}
	
	
	static function GetDomainFromURL($url){
	
		$parts = parse_url($url);
	
		return isset($parts['host']) ? $parts['host'] : false;
	}
	
	
	
}