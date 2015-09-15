<?php

return [
	'visual' => [
		'pixelOne' => [
			'from' => '!\world',
			'to' => '\mind\*'
		]
	],
	'heat' => [
		'handOne' => [
			'from' => '!\world',
			'to' => '\mind\*'
		]
	],
	'motors' => [
		'moveUp' => [
			'to' => '!\world'
		],
		'moveDown' => [
			'to' => '!\world'
		]
	],
	'mind' => [
		'!config' => [
			'generateNeurons' => [
				'count' => '32',
				'typeRelation' => 'random',
				'maxAxons' => '4'
			]
		]
	]
];