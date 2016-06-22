<?php

namespace Command;

class Btc extends \Classes\Command\Base
{
	protected $help = 'BTC functions, use btc help';
	
	protected $usage = 'btc [convert [amount] [currency]]';
	
	protected $verify = false;
	
	protected $numberOfArguments = array(1, 3);
	
	public function command()
	{
		$action = strtoupper($this->arguments[0]);
		switch($action)
		{
			default:
			case 'HELP':
				$this->say($this->usage);
				return;
			break;

			case 'CONVERT':
				$amount = explode('.', sprintf('%.F', $this->arguments[1]));
				$amount = substr($amount[0], 0, 10) . '.' . $amount[1];
				$currency = strtoupper($this->arguments[2]);
				
				$data = $this->fetch('https://blockchain.info/ticker');
				$json = json_decode($data);			
				
				if(count($json) > 0)
				{
					$this->say(sprintf('BTC: (Convert) %s | Currency: %s | Buy Price: %s%.2f | Sell Price: %s%.2f', 
							$amount, $currency, $json->$currency->symbol, ($amount*$json->$currency->buy), $json->$currency->symbol, ($amount*$json->$currency->sell)
					));
					return;
				}
				$this->say('BTC: (Convert) That currency is not supported, or something else went wrong!');
				return;
			break;
		}
	}
}
