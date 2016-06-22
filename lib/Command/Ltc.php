<?php

namespace Command;

class Ltc extends \Classes\Command\Base
{
	protected $help = 'LTC functions, use btc help';
	
	protected $usage = 'ltc [convert [amount] [currency]]';
	
	protected $verify = false;
	
	protected $numberOfArguments = array(1, 3);
	
	public function command()
	{
		$action = strtoupper($this->arguments[0]);
		switch($action)
		{
			default:
			case 'HELP':
				$this->say('LTC: (Help) You must select an action from the following list: Convert');
				return;
			break;

			case 'CONVERT':
				$amount = explode('.', sprintf('%.F', $this->arguments[1]));
				$amount = substr($amount[0], 0, 10) . '.' . $amount[1];
				$currency = strtoupper($this->arguments[2]);
				
				$data = $this->fetch('https://chain.so/api/v2/get_price/LTC/' . $currency);
				$json = json_decode($data);
				
				if($json->status == 'success')
				{
					if(count($json->data->prices) > 0)
					{
						foreach($json->data->prices as $json)
						{
							$price[$json->exchange] = $json->price;
						}
						$this->say(sprintf('LTC: (Convert) %s | Currency: %s | Price: %.2f %s | Exchange: %s', 
								$amount, $currency, ($amount*max($price)), $currency, array_search(max($price), $price)
						));
						return;
					}
					$this->say('LTC: (Convert) That currency is not supported, or something else went wrong!');
					return;
				}
				$this->say('LTC: (Convert) Unable to retrive rates at the moment!');
			break;
		}
	}
}
