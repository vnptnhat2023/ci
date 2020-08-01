<?php

namespace BAPI\Config;

use BAPI\Config\Mixins\ConfigTrait;
use CodeIgniter\Config\BaseConfig;
use Config\Validation;

class User extends BaseConfig
{
	use ConfigTrait;

  private const setting = [

		# --- [ 'user', 'user_group' ]
		'permission' => [
			'all',
			'null',
			'page',
			'category',
			'post',
			'theme',
			'file'
		],

		'db' => [

			'create' => [
				'maximum_rows' => 1
			],

			'update' => [
				'maximum_rows' => 100
			],

			'delete' => [
				'soft' => true,
				'maximum_rows' => 100
			],

			'fetch' => [
				'record' => 10,
				'orderBy' => 'id',
				'direction' => 'ASC'
			],

			'fill' => [
				'group_id ' => 2,
				'status' => 'inactive',
				'cookie_token' => null,
				'last_login' => null,
				'last_activity' => null
			],

			'option' => [
				'default_group' => 2,
				'default_status' => 'inactive',
				'type_confirm' => 'email',
				'email_confirm' => 'api.emailConfirm',
				'avatar' => 'default',# Not using
				'language' => 'vi',# Not using
				'timezone' => 'Asia/HoChiMinh',# Not using
				'welcome' => 'Welcome to website $site_name',# Not using
			]
		]
	];

	public function getRules ( string $key = null ) : array
	{

		$rules = [

			'id' => Validation::ruleInt( 'Id', 'required' ),

			'group_id' => Validation::ruleInt( 'Group-Id' ),

			'created_at' => [ 'label' => 'create At', 'rules' => 'valid_date' ],

			'updated_at' => [ 'label' => 'update At', 'rules' => 'valid_date' ],

			'birthday' => [ 'label' => 'birthday', 'rules' => 'valid_date' ],

			'username' => [
				'label' => 'username',
				'rules' => 'trim|min_length[5]|max_length[32]|alpha_dash'
			],

			'password' => [
				'label' => 'password',
				'rules' => 'trim|min_length[5]|max_length[64]|alpha_numeric_punct'
			],

			'email' => [
				'label' => 'email',
				'rules' => 'trim|min_length[5]|max_length[32]|valid_email'
			],

			'user_group_name' => [
				'label' => 'group Name',
				'rules' => 'trim|min_length[2]|max_length[64]|alpha_numeric_punct'
			],

			'status' => [
				'label' => 'status',
				'rules' => 'required|in_list[active,inactive,banned]'
			],

			'date' => [ 'label' => 'date', 'rules' => 'valid_date' ],

			'fullname' => [
				'label' => 'full Name',
				'rules' => 'trim|min_length[6]|max_length[50]|alpha_space'
			],

			'phone' => [
				'label' => 'phone Number',
				'rules' => 'min_length[10]|max_length[20]|numeric'
			],

			'gender' => [ 'label' => 'gender', 'rules' => 'required|in_list[male,female]' ]
		];

		helper('array');

		return empty( $key ) ? $rules : dot_array_search( $key, $rules );
	}
}