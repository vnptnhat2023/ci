<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Registry;

use Red2Horse\Mixins\Interfaces\Registry\RegistryClass___Interface;
use Red2Horse\Mixins\Traits\Registry\TraitRegistry;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class RegistryClass___ implements RegistryClass___Interface
{
    use TraitRegistry;
}