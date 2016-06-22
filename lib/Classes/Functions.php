<?php

namespace Classes;

/**
 * @package IRCBot
 * @subpackage Classes
 */
class Functions
{
	/**
	 * Removes line breaks from a string.
	 * @param string $string The string with line breaks.
	 * @return string
	 */
	public static function removeLineBreaks( $string )
	{
		return str_replace( array ( chr( 10 ), chr( 13 ) ), '', $string );
	}
}
