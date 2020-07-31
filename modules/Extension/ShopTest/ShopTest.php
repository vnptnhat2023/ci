<?php
namespace Ext\ShopTest;

use App\Libraries\Ext\{ AbstractExtension, ExtensionTrait };
use CodeIgniter\Events\Events;

final class ShopTest extends AbstractExtension
{
	use ExtensionTrait;

	private const map = [
		'author' => 'tester',
		'contact' => 'tester@local.host',
		'category_name' => 'unknown',
		'description' => 'unknown',
		'name' => 'Shop extension',
		'slug' => 'ShopTest',
		'version' => '0.1',
		'events' => [
			[
				'method' => 'index',
				'name' => 'shop-index'
			],
			[
				'method' => 'getMap',
				'name' => 'shop-map'
			]
		]
	];

	/** User define */
	private $data = [];

	/** User define */
	protected $formAttributes = [
		'name' => 'shopTestTemplate',
		'model' => 'shopModel',
	];

	public function index()
	{
		$this->EvRegister( $this->getParameters );
		// $this->data['withParameters'] = $this->getParameters;
		$this->data['inputComponents'] = $this->inputComponents();
		$this->data['rules'] = $this->rules();
		// $this->EvTrigger();

		return $this->data;
	}

	public function getData()
	{
		return $this->getParameters;
	}

	public function EvRegister($params = null)
	{
		Events::on( 'shop.ev', function( callable $ns ) use( $params ) {
			return $ns( $params );
		} );

		$params = new \stdClass;
		$params->one = 'Mot';
		$params->two = 'Hai';
		$params->three = 'Ba';
		Events::on( 'shop.ev.two', function( callable $ns ) use( $params ) {
			return $ns( $params );
		} );
	}

	public function EvTrigger()
	{
		Events::trigger( 'shop.ev', function( $dataDB ) {
			$this->data['shopTriggerData'] = $dataDB;
			$this->data['trigger'] = 'Event from ShopTest';
		} );
	}

	/**
	 * Add this template before rendered page
	 */
	private function inputComponents() : array
	{
		# The template codeIgniter 4 DOCUMENT:
		# guide/docs/outgoing/view_parser.html#nested-substitutions

		# Page title from DB
		$title = ( $this->data[ 'params' ][ 'title' ] ?? 'shop' );
		$componentData = [
			'name' => $this->formAttributes['name'],
			'model' => $this->formAttributes['model'],
			'option' => [
				[
					'value' => 0,
					'label' => $title,
					'selected' => false,
				],
				[
					'value' => 1,
					'label' => "{$title} and all-sub",
					'selected' => true,
				]
			]
		];

		$template = '<select name="{{ name }}">' . PHP_EOL;
		$template .= "\t" . '{{ option }}';
		$template .= '<option value="{{ value }}" selected="{{ selected }}">{{ label | capitalize }}</option>';
		$template .= '{{ /option }}' . PHP_EOL . '</select>' . PHP_EOL;

		$vueTemplate = '<select :name="name" v-model="model">' . PHP_EOL;
		$vueTemplate .= "\t" . '<option v-for="s in option" :value="s.value" :selected="s.selected">';
		$vueTemplate .= '{{ s.label | capitalize }}</option>' . PHP_EOL;
		$vueTemplate .= '</select>' . PHP_EOL;

		$components['data'] = $componentData;
		$components['template'] = $template;
		$components['vueTemplate'] = $vueTemplate;

		return $components;
	}

	/** Add validation rule before input submitted */
	private function rules() : array
	{
		return [
			'test' => 'required|max_length[128]',
			'more' => 'if_exist|required'
		];
	}
}