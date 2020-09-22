<?php

if ( ! function_exists('strHighestVersion') )
{
  /**
   * Find highest version an array
   * @param array $haystack
   * @param string $condition
   * @return string
   * @example array ['1.0.8', '1.0.9', '1.0.10']
   * @source url https://stackoverflow.com/questions/35599367/how-to-get-the-highest-version-number-tag-in-php
   */
  function strHighestVersion(array $haystack, string $condition = '>') : string
  {
    $str = array_reduce( $haystack, function ( $highest, $current ) use( $condition )
    {
      return version_compare( $highest, $current, $condition ) ? $highest : $current;
    });

    # --- Or using usort($haystack, 'version_compare'); echo end($versions);

    return $str;
  }
}

if ( ! function_exists( 'strCamelCase' ) )
{
  /**
	 * @package CodeIgniter4
   * Convert string to CamelCaseUpperFirstLetter
   */
  function strCamelCase ( string $str ) : string
  {
    return str_replace( ' ', '', ucwords( str_replace( [ '-', '_' ], ' ', $str ) ) );
  }
}