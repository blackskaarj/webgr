<?php
class Default_Mail extends Zend_Mail {
	
	private $transporter;
	
	public function __construct($toAdress, $subject, $bodyText)
	{
		parent::__construct('utf-8');
		$emailArray = Zend_Registry::get('MAIL_CONF');
		$config = array (  'auth'  => 'login',
                            'username'  =>  $emailArray->username,
                            'password'  =>  $emailArray->password,
                            'port'      => $emailArray->port);

                if($emailArray->useSSL){
                    $config['ssl'] = 'tls';
                }
                
		$this->transporter = null;
		//check for mailers which don't use authentification
		if($config['password'] == '' || $config['password'] == null){
			$this->transporter = new Zend_Mail_Transport_Smtp($emailArray->host);
		}else{
			$this->transporter = new Zend_Mail_Transport_Smtp($emailArray->host,$config);
		}
		$this->setFrom($emailArray->fromAdress, 'WEB_GR');
		$this->addTo($toAdress);
		$this->setSubject($subject);
		$introBody = "This is an auto generated message from WebGR.\r\n";
		$this->setBodyText($introBody.$bodyText);
	}
	
	public function send($transport = null)
	{
		parent::send($this->transporter);
	}
}