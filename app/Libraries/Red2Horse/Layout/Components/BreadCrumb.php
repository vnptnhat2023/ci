<?php

declare( strict_types = 1 );
namespace Red2Horse\Layout\Components;

use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Breadcrumb
{
    use TraitSingleton;

    private array $data;

    public string $baseLink;

    public array $breadCrumb;

    public function __construct ( array $data )
    {
        $this->data = $data;
    }

    public function init ( array $data )
    {
        foreach ( $this->data as $key => $value )
        {
            
        }
    }
}