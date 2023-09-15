<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

class Validation
{
	use TraitSingleton;

	# Form input name
	public const USERNAME = 'username';
	public const PASSWORD = 'password';
	public const EMAIL = 'email';
	public const CAPTCHA = 'captcha';

	# Form input group
	public const LOGIN = 'login';
	public const LOGIN_WITH_CAPTCHA = 'login_captcha';
	public const FORGET = 'forget';
	public const FORGET_WITH_CAPTCHA = 'forget_captcha';

	# Rule groups
	public array $ruleGroup = [
		self::LOGIN => [
			self::USERNAME,
			self::PASSWORD
		],
		self::LOGIN_WITH_CAPTCHA => [
			self::USERNAME,
			self::PASSWORD,
			self::CAPTCHA
		],

		self::FORGET => [
			self::USERNAME,
			self::EMAIL
		],
		self::FORGET_WITH_CAPTCHA => [
			self::USERNAME,
			self::EMAIL,
			self::CAPTCHA
		]
	];
}