<?php

/**
 * Generate general relational data
 * @param int $maxId highest id number of main column
 * @param int $rows how many rows to fill
 * @param int $groupId id of general group
 * @param int $nameId id of $name variable
 * @param string $name po, ca, pa, po, ex, ...
 * @return array [ name, name_id, ggid, giid ]
 */
function generateGeneralRelation (
	int $maxId,
	int $rows,
	int $groupId,
	int $nameId,
	string $name = 'ex'
) : array
{
	if ( $rows <= 0 || $maxId < 0 ) return [];

	$sumRow = ( $rows + ( $maxId ++ ) );

	if ( $maxId >= $sumRow ) return [];

	$data = [];
	$range = range( $maxId, $sumRow );

	foreach ( $range as $itemId ) {
		$data[] = [
			'name' => $name,
			'name_id' => $nameId,
			'ggid' => $groupId,
			'giid' => $itemId
		];
	}

	return $data;
}

/**
 * Handle general relational data
 * @param int $nameId
 * @param array $haystack
 * an array with 3 level inside
 * ``
 * $haystack = [ "id_11" => [ [ "id" => 5 ] ] , ...]
 * ``
 * @param string $name default "po"
 * @return array
 */
function handleGeneralRelation (
	int $nameId,
	array $haystack = [],
	string $name = 'po'
) : array
{
	$data = [];
	$groupIds = [];
	$itemIds = [];
	$error = [];

	foreach ( $haystack as $key => $container ) {

		if ( ! is_array( $container ) || empty( $container ) ) { continue; }

		foreach ( $container as $value ) {

			$groupId = str_replace( 'id_', '', $key );
			$itemId = isset( $value[ 'id' ] ) ? (string) $value[ 'id' ] : null;

			if ( '0' === $groupId || !ctype_digit( $groupId ) )
			{
				$error[] = lang( 'Validation.is_natural_no_zero', [ 'field' => 'group id' ] );
				break 2;
			}
			else if ( '0' === $itemId || !ctype_digit( $itemId ) )
			{
				$error[] = lang( 'Validation.is_natural_no_zero', [ 'field' => 'item id' ] );
				break 2;
			}

			# --- Todo: check error, return when have
			# --- Todo: model from arg, check ids exist

			$data[] = [
				'name' => $name,
				'name_id' => $nameId,
				'ggid' => $groupId,
				'giid' => $itemId
			];

			$groupIds[] = $groupId;
			$itemIds[] = $itemId;
		}
	}

	if ( ! empty( $error ) ) return $error;


	$response = [
		'data' => $data,
		'group_id' => array_keys( array_flip( $groupIds ) ),
		'item_id' => $itemIds
	];

  return $response;
}