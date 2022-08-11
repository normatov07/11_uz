<?php

interface SmsAggregator {

	public function getRequest();
	public function sendMessage($message, $sync = FALSE);
	
}
