<?php

class paynet_response {
	
	public function format()
	{
		if (Router::$controller == 'paynet')
		{
			Event::$data = str_replace('SOAP-ENV', 'soapenv', Event::$data);
		}
	}
}

Event::add('system.display', array('paynet_response', 'format'));