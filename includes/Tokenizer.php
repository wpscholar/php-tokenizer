<?php

namespace wpscholar\PhpTokenizer;

/**
 * Class Tokenizer
 *
 * @package wpscholar\PHP\Tokenizer\
 */
class Tokenizer {

	/**
	 * A key indexed collection of tokens.
	 *
	 * @var Token[]
	 */
	protected $tokens = [];

	/**
	 * A collection of token indexes indexed by code.
	 *
	 * @var array
	 */
	protected $tokensByCode = [];

	/**
	 * A collection of token indexes indexed by md5 content hashes.
	 *
	 * @var array
	 */
	protected $tokensByContent = [];

	/**
	 * A collection of token indexes indexed by token hash.
	 *
	 * @var array
	 */
	protected $tokensByHash = [];

	/**
	 * A collection of token indexes indexed by line number.
	 *
	 * @var array
	 */
	protected $tokensByLine = [];

	/**
	 * A collection of token indexes indexed by token type.
	 *
	 * @var array
	 */
	protected $tokensByType = [];

	/**
	 * Parse a file into tokens.
	 *
	 * @param string $file
	 *
	 * @return Tokenizer
	 */
	public static function fromFile( $file ) {
		return new self( Tokenizer::tokenize( file_get_contents( $file ) ) );
	}

	/**
	 * Parse a string into tokens.
	 *
	 * @param $string
	 *
	 * @return Tokenizer
	 */
	public static function fromString( $string ) {
		return new self( Tokenizer::tokenize( $string ) );
	}

	/**
	 * Create a new instance with a collection of existing tokens.
	 *
	 * @param array $tokens
	 *
	 * @return Tokenizer
	 */
	public static function fromCollection( $tokens = [] ) {
		return new self( $tokens );
	}

	/**
	 * Convert a string to a collection of tokens.
	 *
	 * @param string $raw
	 *
	 * @return Token[]
	 */
	public static function tokenize( $raw ) {
		$collection = [];

		$tokens = token_get_all( $raw );
		foreach ( $tokens as $key => $value ) {

			$token          = new Token();
			$token->index   = $key;
			$token->code    = is_string( $value ) ? 0 : $value[0];
			$token->type    = is_string( $value ) ? $value : token_name( $value[0] );
			$token->content = is_string( $value ) ? $value : $value[1];
			$token->line    = is_string( $value ) ? 0 : $value[2];

			$collection[ $key ] = $token;
		}

		return $collection;
	}

	/**
	 * Tokenizer constructor.
	 *
	 * @param array $tokens
	 */
	protected function __construct( array $tokens = [] ) {
		if ( ! empty( $tokens ) ) {
			array_walk(
				$tokens,
				function ( Token $token, $key ) {

					$token_hash   = spl_object_hash( $token );
					$content_hash = md5( $token->content );

					$token->index = $key;

					$this->tokens[ $key ] = $token;

					$this->tokensByCode[ $token->code ][]     = $key;
					$this->tokensByContent[ $content_hash ][] = $key;
					$this->tokensByHash[ $token_hash ][]      = $key;
					$this->tokensByLine[ $token->line ][]     = $key;
					$this->tokensByType[ $token->type ][]     = $key;
				}
			);
		}
	}

	/**
	 * Get all tokens as an array.
	 *
	 * @return Token[]
	 */
	public function toArray() {
		return $this->tokens;
	}

	/**
	 * Get a token by index.
	 *
	 * @param int $index
	 *
	 * @return Token|null
	 */
	public function getByIndex( $index ) {
		return isset( $this->tokens[ $index ] ) ? $this->tokens[ $index ] : null;
	}

	/**
	 * Get the first token in the collection matching a specific value.
	 *
	 * @param string|int $value
	 * @param string     $property
	 *
	 * @return Token|null
	 */
	public function first( $value, $property = 'type' ) {
		$tokens = $this->filter( $value, $property );

		return empty( $tokens ) ? null : array_shift( $tokens );
	}

	/**
	 * Get the last token in the collection matching a specific value.
	 *
	 * @param        $value
	 * @param string $property
	 *
	 * @return mixed|null
	 */
	public function last( $value, $property = 'type' ) {
		$tokens = $this->filter( $value, $property );

		return empty( $tokens ) ? null : array_pop( $tokens );
	}

	/**
	 * Filter the collection of tokens by a specific property and value.
	 *
	 * @param        $value
	 * @param string $property
	 *
	 * @return array
	 */
	public function filter( $value, $property = 'index' ) {
		switch ( strtolower( $property ) ) {
			case 'index':
				return isset( $this->tokens[ $value ] ) ? [ $this->tokens[ $value ] ] : [];
			case 'code':
				if ( ! isset( $this->tokensByCode[ $value ] ) ) {
					break;
				}

				return array_map( [ $this, 'getByIndex' ], $this->tokensByCode[ $value ] );
			case 'content':
				if ( ! isset( $this->tokensByContent[ md5( $value ) ] ) ) {
					break;
				}

				return array_map( [ $this, 'getByIndex' ], $this->tokensByContent[ md5( $value ) ] );
			case 'hash':
			case 'token':
				if ( ! isset( $this->tokensByHash[ spl_object_hash( $value ) ] ) ) {
					break;
				}

				return array_map( [ $this, 'getByIndex' ], $this->tokensByHash[ spl_object_hash( $value ) ] );
			case 'line':
				if ( ! isset( $this->tokensByLine[ $value ] ) ) {
					break;
				}

				return array_map( [ $this, 'getByIndex' ], $this->tokensByLine[ $value ] );
			case 'type':
				if ( ! isset( $this->tokensByType[ $value ] ) ) {
					break;
				}

				return array_map( [ $this, 'getByIndex' ], $this->tokensByType[ $value ] );
		}

		return [];
	}

	/**
	 * Get all tokens after a specific index.
	 *
	 * @param int $index
	 *
	 * @return Token[]
	 */
	public function after( $index ) {
		return array_slice( $this->tokens, $index + 1 );
	}

	/**
	 * Get all tokens before a specific index.
	 *
	 * @param int $index
	 *
	 * @return Token[]
	 */
	public function before( $index ) {
		return array_slice( $this->tokens, 0, $index );
	}

}
