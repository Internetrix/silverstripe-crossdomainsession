<?php
class CrossDomainControllerExtension extends DataExtension {
	
	public function modelascontrollerInit(SiteTree $SiteTree) {
		
		$request = Controller::curr()->request;
		
		$crossDomainHash = Convert::raw2sql($request->getVar('gocrossdomain'));
		
		if($crossDomainHash !== null && $crossDomainHash){
			
			$userIP = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
			
			$CrossDomainRequest = CrossDomainRequest::get()->filter(array(
					'Hash' 		=> $crossDomainHash,
					'IP'		=> $userIP,
					'Finished' 	=> false
			))->first();

			if($CrossDomainRequest && $CrossDomainRequest->ID){
				// cross domain session request is valid
				$sessionDataArray = unserialize($CrossDomainRequest->Data);

				if( ! empty($sessionDataArray)){
					
					foreach ($sessionDataArray as $name => $val){
						Session::set($name, $val);
					}
					
					Session::save();
				}
				
				$CrossDomainRequest->Finished = true;
				$CrossDomainRequest->write();
				
				$destURL = Director::absoluteURL($_SERVER['REQUEST_URI']);
				
				//remove 'gocrossdomain' param
				$destURL = str_ireplace('gocrossdomain=', 'cdsid=', $destURL);
				
				header("Location: $destURL", true, 301);
				die('<h1>Your browser is not accepting header redirects</h1><p>Please <a href="'.$destURL.'">click here</a>');
			}
			
		}
		
	}
	
	

	
}