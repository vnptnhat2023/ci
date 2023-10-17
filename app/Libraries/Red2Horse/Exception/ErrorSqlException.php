<?php

declare( strict_types = 1 );
namespace Red2Horse\Exception;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class ErrorSqlException extends \Exception
{
    public function __construct ( ?string $message = null, ?int $code = null )
    {
        parent::__construct( $message ?? 'Invalid array format.', $code ?? 406 );
    }
}