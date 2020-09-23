<?php

function dotArrayAssign ( array &$arr, string $path, $value, string $separator = '.' )
{
	$keys = explode( $separator, $path );

	foreach ( $keys as $key ) {
		$arr = &$arr[ $key ];
	}

	$arr = $value;
}

# $cutNum = how many items wanna cut
# array_splice($array, count($array) - $cutNum, $cutNum);

/**
 * Check duplicates array
 * https://stackoverflow.com/questions/3145607/php-check-if-an-array-has-duplicates
 */
function arrayHasDupes ( array $input_array ) : bool
{
  return count( $input_array ) !== count( array_flip( $input_array ) );
}

/**
 * - Take on internet
 * - https://stackoverflow.com/questions/8840319/build-a-tree-from-a-flat-array-in-php
*/
function buildTree ( array &$elements, $parentId = 0, bool $flat = false )
{
	$branch = [];

	foreach ( $elements as &$element )
	{
    if ( $element['parent_id'] == $parentId ) {

      $children = buildTree( $elements, $element['id'] );

      if ( $children ) {
        if ( true === $flat )
        {
          $cKey = key( $children );
          $branch[ $cKey ] = $children[ $cKey ];
        }
        else
        {
          $element['children'] = $children;
        }
      }

      $branch[ $element['id'] ] = $element;
      unset( $element );
    }
	}

  return $branch;
}

function arrayPrint ( $data, bool $json = false ) : string
{
  helper( 'text' );

  $type = gettype($data);

  if ( is_array($data) OR is_object($data) )
  {
    $str = $json
      ? highlight_code( json_encode( $data, JSON_PRETTY_PRINT ) )
      : highlight_code( var_export( $data, true ) );
  }
  else if ( is_string($data) )
  {
    $str = highlight_code($data);
  }
  else
  {
    $str = 'Unsupported: ' . gettype($data);
  }

  $json = $json ? 'true' : 'false';
  return "<pre><div style=\"text-align:right\">
  Type:<u>{$type}</u> | JSON: <u>{$json}</u></div><br>$str</pre>";
}

function arrayDump ( array $data, string $title = '' ) : string
{
  return $title . PHP_EOL . var_export( $data, true ). PHP_EOL;
}


function arraySearchCustom ( string $search = '', array $array )
{
  if ( 0 == strlen($search) OR ! is_array($array) ) return false;
  if ( ! in_array($search, $array) ) return false;

  return strlen(array_search($search, $array)) > 0
    ? (int) array_search( $search, $array )
    : false;
}

/**
 * @function xoa 1 element
 * @param string
 * @param array
 * */
function arrayDeleteByValue ( string $search = '', array $array )
{
  if ( FALSE === ( $key = arraySearchCustom( $search, $array ) ) ) return false;
  unset( $array[ $key ] );

  return array_values( $array );
}

function side_bar_unique ( array $array, array $array_search, array $colum_name ) :array
{
  $column_search = array_column( $array_search, $colum_name );
  $column_delete = array_column( $array, $colum_name );

  foreach ( $column_search as $needed )
  {

    if ( in_array( $needed, $column_delete ) )
    {

      if ( strlen( array_search( $needed, $column_delete ) ) > 0 )
      {
        $key = array_search( $needed, $column_delete );
        unset( $array[ $key ] );
			}

		}

	}

  return array_values( $array );
}

function isAssoc ( array $data ) : bool
{
	if ( [] === $data ) {
		return false;
	}

  return array_keys( $data ) !== range( 0, count($data) - 1 );
}

/**
 * @param variable
 * @param set return
 * @return var $return
 */
function isVar ( & $var, $return = null )
{
  return $var ?? $return;
}

if ( ! function_exists( 'array_key_first' ) )
{
  function array_key_first ( array $array )
  {
    if ( ! is_array( $array ) || empty( $array ) ) {
      return NULL;
    }

    return key( $array )[ 0 ];
  }
}

if ( ! function_exists( 'array_key_last' ) )
{
  function array_key_last ( array $array )
  {
    if ( ! is_array( $array ) || empty( $array ) ) {
      return NULL;
    }

    return array_keys( $array )[ count( $array ) - 1 ];
  }
}

if ( ! function_exists( 'array_key_first_last' ) )
{
  function array_key_first_last ( array $array )
  {
    if ( ! is_array( $array ) || empty( $array ) ) {
      return NULL;
    }

    return [
      'first' => $array[ array_key_first( $array ) ],
      'last' => $array[ array_key_last( $array ) ]
    ];
  }
}