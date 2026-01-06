<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace MarketPress\German_Market\Symfony\Component\Yaml;

use MarketPress\German_Market\Symfony\Component\Yaml\Exception\ParseException;

/**
 * Unescaper encapsulates unescaping rules for single and double-quoted
 * YAML strings.
 *
 * @author Matthew Lewinski <matthew@lewinski.org>
 *
 * @internal
 */
class Unescaper
{
    /**
     * Regex fragment that matches an escaped character in a double quoted string.
     */
    public const REGEX_ESCAPED_CHARACTER = '\\\\(x[0-9a-fA-F]{2}|u[0-9a-fA-F]{4}|U[0-9a-fA-F]{8}|.)';

    /**
     * Unescapes a single quoted string.
     *
     * @param string $value A single quoted string
     */
    public function unescapeSingleQuotedString(string $value): string
    {
        return str_replace('\'\'', '\'', $value);
    }

    /**
     * Unescapes a double quoted string.
     *
     * @param string $value A double quoted string
     */
    public function unescapeDoubleQuotedString(string $value): string
    {
        $callback = fn ($match) => $this->unescapeCharacter($match[0]);

        // evaluate the string
        return preg_replace_callback('/'.self::REGEX_ESCAPED_CHARACTER.'/u', $callback, $value);
    }

    /**
     * Unescapes a character that was found in a double-quoted string.
     *
     * @param string $value An escaped character
     */
    private function unescapeCharacter(string $value): string
    {
    	if ( '0' === $value[ 1 ] ) {
    		return "\x0";
    	} else if ( 'a'=== $value[ 1 ] ) {
    		return "\x7";
    	} else if ( 'b'=== $value[ 1 ] ) {
    		return "\x8";
    	} else if ( 't' === $value[ 1 ] ) {
    		return "\t";
    	} else if ( "\t" === $value[ 1 ] ) {
    		return "\t";
    	} else if ( "\n" === $value[ 1 ] ) {
    		return "\n";
    	} else if ( "v" === $value[ 1 ] ) {
    		return "\x1B";
    	} else if ( "f" === $value[ 1 ] ) {
    		return "\xC";
    	} else if ( "r" === $value[ 1 ] ) {
    		return "\r";
    	} else if ( "e" === $value[ 1 ] ) {
    		return "\x1B";
    	} else if ( " " === $value[ 1 ] ) {
    		return ' ';
    	} else if ( '"' === $value[ 1 ] ) {
    		return '"';
    	} else if ( '/' === $value[ 1 ] ) {
    		return '/';
    	} else if ( '\\' === $value[ 1 ] ) {
    		return '\\';
    	} else if ( 'N' === $value[ 1 ] ) {
    		return "\xC2\x85";
    	} else if ( '_' === $value[ 1 ] ) {
    		return "\xC2\xA0";
    	} else if ( 'L' === $value[ 1 ] ) {
    		return "\xE2\x80\xA8";
    	} else if ( 'P' === $value[ 1 ] ) {
    		return "\xE2\x80\xA9";
    	} else if ( 'x' === $value[ 1 ] ) {
    		return self::utf8chr(hexdec(substr($value, 2, 2)));
    	} else if ( 'u' === $value[ 1 ] ) {
    		return self::utf8chr(hexdec(substr($value, 2, 4)));
    	} else if ( 'U' === $value[ 1 ] ) {
    		return self::utf8chr(hexdec(substr($value, 2, 8)));
    	} else {
    		throw new ParseException(sprintf('Found unknown escape character "%s".', $value));
    	}
    }

    /**
     * Get the UTF-8 character for the given code point.
     */
    private static function utf8chr(int $c): string
    {
        if (0x80 > $c %= 0x200000) {
            return \chr($c);
        }
        if (0x800 > $c) {
            return \chr(0xC0 | $c >> 6).\chr(0x80 | $c & 0x3F);
        }
        if (0x10000 > $c) {
            return \chr(0xE0 | $c >> 12).\chr(0x80 | $c >> 6 & 0x3F).\chr(0x80 | $c & 0x3F);
        }

        return \chr(0xF0 | $c >> 18).\chr(0x80 | $c >> 12 & 0x3F).\chr(0x80 | $c >> 6 & 0x3F).\chr(0x80 | $c & 0x3F);
    }
}
