<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Password;

use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;
use function Red2Horse\Mixins\Functions\Instance\getComponents;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function getHashPass ( string $str ) : string
{
    return getBaseInstance( 'password' )->getHashPass( $str );
}

function getVerifyPass ( string $password, string $hashed ) : bool
{
    return getBaseInstance( 'password' )->getVerifyPass( $password, $hashed );
}

function getRandomString ( string $str ) : string
{
    $common = getComponents( 'common' );
    $randomString = $common->random_string( 'alnum', rand( 8, 32 ) );
    $randomString2 = $common->random_string( 'alnum', rand( 8, 32 ) );
    $str = $randomString . $str . $randomString2;
    
    $hashes = [
        [ 'md5' => rand( 1, 4 ) ],
        [ 'sha1' => rand( 1, 4 ) ],
    ];
    shuffle( $hashes );
    $a = 1;

    foreach ( $hashes as $value )
    {
        foreach ( $value as $hash => $nums )
        {
            while( $a <= $nums ) { $str = $hash( $str ); $a++; }
            $str = $hash( $str );
        }
    }

    return $str;
}