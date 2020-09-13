<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

// use App\Libraries\Red2Horse\Facade\{
// 	Mail\MailFacade as mail
// };

class Authorization
{
	use TraitSingleton;

	protected Config $config;

	public function __construct( Config $config )
	{
		$this->config = $config;

		// $builder = AuthComponentBuilder::createBuilder( $config )
		// ->mail()
		// ->build();

		// $this->mail = $builder->mail;
	}
}