<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.

// Note: on server failure, partition masters should be switched to the slave
$wgJobTypeConf['default'] = array(
	'class'               => 'JobQueueFederated',
	'configByPartition'   => array(
		'rdb1' => array(
			'class'       => 'JobQueueRedis',
			'redisServer' => '10.64.32.76', # rdb1001 (master)
			#'redisServer' => '10.64.32.77', # rdb1002 (slave)
			'redisConfig' => array(
				'connectTimeout' => 2,
				'password' => $wmgRedisPassword,
				'compression' => 'gzip'
			)
		),
		'rdb2' => array(
			'class'       => 'JobQueueRedis',
			'redisServer' => '10.64.0.201', # rdb1003 (master)
			#'redisServer' => '10.64.16.183', # rdb1004 (slave)
			'redisConfig' => array(
				'connectTimeout' => 2,
				'password' => $wmgRedisPassword,
				'compression' => 'gzip'
			)
		),
	),
	'sectionsByWiki'      => array(), // default
	'partitionsBySection' => array(
		'default' => array( 'rdb1' => 50, 'rdb2' => 50 ),
	)
);
// Note: on server failure, this should be changed to any other redis server
$wgJobQueueAggregator = array(
	'class'       => 'JobQueueAggregatorRedis',
	'redisServer' => '10.64.0.180', # mc1001
	'redisConfig' => array(
		'connectTimeout' => 2,
		'password' => $wmgRedisPassword,
	)
);

// Moved to its own job pipeline
$wgJobTypesExcludedFromDefaultQueue += array(
	'ParsoidCacheUpdateJob', // TODO: remove once queue is drained.
	'ParsoidCacheUpdateJobOnEdit',
	'ParsoidCacheUpdateJobOnDependencyChange'
);

