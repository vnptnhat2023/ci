<?php

function handleMedia ( array $data ) : array
{
  if ( empty( $data[ 'images' ] ) && empty( $data[ 'videos' ] ) ) return [];

	/**
	 * Reference data
	 * @var array $mediaData
	 */
	$mediaData = [];

	# --- Image filter
  if ( isset( $data[ 'images' ] ) && is_array( $data[ 'images' ] ) ) {
    handleImage( $data[ 'images' ], $mediaData );
  }

	# --- Video filter
  if ( isset( $data[ 'videos' ] ) && is_array( $data[ 'videos' ] ) ) {
		handleVideo( $data[ 'videos' ], $mediaData );
	}

	return $mediaData;
}

/**
 * Handle **image** data
 */
function handleImage( array $data, array & $response ) : void
{
	foreach ( $data as $value )
	{
		$response[] = [
			'name' => 'image',
			'value' => json_encode( $value )
		];
	}
}

/**
 * Handle **video** data
 */
function handleVideo ( array $data, array & $response ) : void
{
	# http.(s)\:\/\/.(www)\.ok\.ru/\video\/
	$search = [
		'https://www.youtube.com/watch?v=',
		'http://www.youtube.com/watch?v=',
		'https://youtube.com/watch?v=',
		'http://youtube.com/watch?v=',
		'https://ok.ru/video/',
		'https://www.ok.ru/video/',
		'https://m.ok.ru/video/',
		'https://ok.ru/videoembed/',
		'https://www.ok.ru/videoembed/',
	];

	$respond = fn( array $v ) => [
		'name' => 'video',
		'value' => json_encode( $v )
	];

	$encrypt = fn( array $v ) => service( 'encrypter' )->encrypt(
		str_replace( $search, '', $v[ 'link' ] )
	);

	foreach ( $data as $v2 )
	{
		if ( ! filter_var( $v2[ 'link' ], FILTER_VALIDATE_URL ) ) { continue; }

		$hostLink = url_hostname( $v2[ 'link' ] );

		if ( $hostLink === 'youtube.com' )
		{
			$v2[ 'link' ] = $encrypt( $v2 );
			$v2[ 'type' ] = 'youtube';
		}
		else if ( $hostLink === 'facebook.com' )
		{
			$v2[ 'link' ] = service('encrypter')->encrypt( $v2[ 'link' ] );
			$v2[ 'type' ] = 'facebook';
		}
		else if ( $hostLink === 'ok.ru' )
		{
			$v2[ 'link' ] = $encrypt( $v2 );
			$v2[ 'type' ] = 'ok';
		}

		$response[] = $respond( $v2 );
	}
}

/**
 * Generate media relational, return data [ data, media_id ] | []
 * @param int $maxId highest id number of main column
 * @param int $rows how many rows to fill
 * @param string $colName category_id, post_id, page_id, ...
 * @param string $colValue
 * @return array [ data, media_id ] | [] when data is not valid
 */
function generateMediaRelation (
	int $maxId,
	int $rows,
	string $colName = 'post_id',
	string $colValue = null
) : array
{
	if ( $rows <= 0 || $maxId < 0 ) return [];

	$sumRow = ( $rows + ( $maxId ++ ) );

	if ( $maxId >= $sumRow ) return [];

	$data = [];
	$mediaIds = [];
	$range = range( $maxId, $sumRow );

	foreach ( $range as $mediaId ) {
		$data[] = [
			'media_id' => $mediaId,
			$colName => $colValue,
			'user_id' => service( 'NknAuth' )->get_userdata( 'id' )
		];

		$mediaIds[] = $mediaId;
	}

	# --- Todo: check empty $data
	# --- Todo: model from arg -> check ids exist
	return [
		'data' => $data,
		'media_id' => array_keys( array_flip( $mediaIds ) )
	];
}

/**
 * Generate media relational data with **id** decrement, return [ data, media_id ]
 * @read_more generateMediaRelation
 */
function generateMediaRelationDecrement(
	int $maxId,
	int $rows,
	string $colName = 'post_id',
	$colValue = null
) : array
{
	$maxId = ( $maxId + $rows );

	$countFor = ( $maxId - $rows );

	if ( $maxId <= $countFor ) return [];

	$data = [];

	for ( $i = $maxId; $i > $countFor; $i -- )
	{
		$data[] = [
			'media_id' => $i,
			$colName => $colValue,
			'user_id' => service( 'NknAuth' )->get_userdata( 'id' )
		];

		$mediaIds[] = $i;
	}

	return [
		'data' => $data,
		'media_id' => array_keys( array_flip( $mediaIds ) )
	];
}