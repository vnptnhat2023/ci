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

if ( ! function_exists('strCamelCase') )
{
  /**
   * Conver string to CamelCaseUpperFirstLetter
   */
  function strCamelCase(string $str) : string
  {
    $str = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $str)));

    return $str;
  }
}


if ( ! function_exists('mb__ucfirst') )
{
  function mb__ucfirst(string $str, bool $esc = FALSE) : string
  {
    $str = $esc ? esc($str) : $str;
    if ( ! extension_loaded('mbstring')) return strtolower($str);

    $str = mb_strtolower($str);
    return mb_convert_case(mb_substr($str, 0, 1), MB_CASE_TITLE) . mb_substr($str, 1);
  }
}

if ( ! function_exists('mb_strtolower') )
{
  function mb_strtolower(string $str, bool $esc = FALSE) : string
  {
    $str = $esc ? esc($str) : $str;

    if ( extension_loaded('mbstring') ) {
      return mb_strtolower($str);
    }

    return strtolower($str);
  }
}