<?php

return array(
	/*
	 * This is the command prefix that'll execute commands for the bot.
	 */
	'prefix' => '.',

	/*
	 * This is the SSL setting, true/false;
	 */
	'serverssl' => false,

	/*
	 * This is the Server Host that the bot will connect to.
	 */
	'serveraddr'	=> 'localhost',
	
	/*
	 * This is the Server Port that the bot will connect to.
	 */
	'serverport'		=> 6667,	

	/*
	 * Set this to the OLine you like;
	 */
	'operline' 		=> array(
					'username', 'password'
	),

	/*
    * Set this to the hostname you want, if operline exists above;
    */
	'hostline'		=> 'host.tld',

	/*
	 * This is the Socks5 Array that the socket will use if enabled.
	 */
	'socks5'		=> array(
				#This will enable to disable socks5 proxy
				'enabled'	=> false,
				#This is the server Host for the socks5 proxy
				'server' 	=> 'localhost',
				#This is the server Port for the socks5 proxy
				'port'		=> 9050
	),

	/*
	 * This is the user modes to set on the bot.
	 */
	'modes'		=> '-x',
	
	/*
	 * This is the Nickname for the bot, aka. Ident/NICK
	 */
	'nickname'	  => 'Ghost',
	
	/*
	 * This is the Realname for the bot, aka. Ident/USER.
	 */
	'username'	  => 'nobody',
	
	/*
	 * This is the Password for the Irc...
	 */
	'password' => '',
	
	/*
	 * This is the Channels that the bot will join.
	 */
	'channels' => array(
			'#php'
    ),
	
	/*
	 * This is the Timezone the bot will use.
	 */
	'timezone' => 'America/New_York',
	
	/*
	 * This is how many times the bot will attempt to reconnect.
	 */
	'max_reconnects' => 2,

	/*
	 * Set this to the trusted hostmasks, that will be able to do trusted things.
	 */
	'hosts' => array(
				'nick1!user1@0.0.0.0',
				'nick2!user2@0.0.0.0'
    ),
    
   /*
    *	Set this to the Authentication Password you want for /NOTICE Bot Password
    *  in the Authenticate Listener.
	 */
	'authpass'	=> 'AUTHME'
);
