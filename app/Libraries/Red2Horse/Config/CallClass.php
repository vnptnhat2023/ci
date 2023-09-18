<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class CallClass
{
	use TraitSingleton;

    public bool $traitUseBefore = false;
    public bool $traitUseAfter = false;
    public string $traitBeforePrefix = 'R2h_before_';
    public string $traitAfterPrefix = 'R2h_after_';

}