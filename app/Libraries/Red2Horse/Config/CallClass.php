<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

/** Only set in {setConfig} function. */
class CallClass
{
	use TraitSingleton;
    /*
	|--------------------------------------------------------------------------
	| CallClass
	|--------------------------------------------------------------------------
	*/
    public bool $traitUseBefore = false;
    public bool $traitUseAfter = false;
    public string $traitBeforePrefix = 'R2h_before_';
    public string $traitAfterPrefix = 'R2h_after_';

}