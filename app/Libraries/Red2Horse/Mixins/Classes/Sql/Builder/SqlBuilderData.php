<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Sql\Builder;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class SqlBuilderData
{
    use TraitSingleton;

    public      int         $updateLimitRows        = 1000;
    public      int         $deleteLimitRows        = 1000;
    public      int         $getLimitRows           = 1000;

    public     array       $select                 = [];
    public     array       $distinct               = [];
    public     array       $between                = [];
    public     array       $insert                 = [];
    public     array       $from                   = [];
    public     array       $andWhere               = [];
    public     array       $orWhere                = [];
    public     array       $where                  = [];
    public     array       $join                   = [];
    public     array       $set                    = [];
    public     array       $limit                  = [];

    // ON
    public     array       $andOn                  = [];
    public     array       $orOn                   = [];
    public     array       $on                     = [];

    // IN   
    public     array       $in                     = [];
    public     array       $notIn                  = [];
    public     array       $orIn                   = [];
    public     array       $orNotIn                = [];
    public     array       $andIn                  = [];
    public     array       $andNotIn               = [];

    // LIKE 
    public     array       $like                   = [];
    public     array       $orLike                 = [];
    public     array       $andLike                = [];

    // NULL 
    public     array       $null                   = [];
    public     array       $notNull                = [];
    public     array       $orNull                 = [];
    public     array       $orNotNull              = [];
    public     array       $andNull                = [];
    public     array       $andNotNull             = [];
    public     array       $orderBy                = [];

    public function reset () : void
    {
        $props = get_object_vars( $this );
        
        foreach( $props as $name => $value )
        {
            if ( gettype( $this->$name ) == 'array' )
            {
                $this->$name = [];
            }
        }
    }
}