<?php

namespace wpscholar\PhpTokenizer;

/**
 * Class Token
 *
 * @package wpscholar\PHP\Tokenizer
 */
class Token {

	/**
	 * The index in relation to all the other tokens.
	 *
	 * @var int
	 */
	public $index;

	/**
	 * The code used to lookup the type.
	 *
	 * @var int
	 */
	public $code;

	/**
	 * The content of the token.
	 *
	 * @var string
	 */
	public $content;

	/**
	 * The line of code where the token was found.
	 *
	 * @var int
	 */
	public $line;

	/**
	 * The type of token.
	 *
	 * @see https://www.php.net/manual/en/tokens.php
	 *
	 * @var string
	 */
	public $type;

}
