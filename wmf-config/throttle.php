<?php

# WARNING: This file is publically viewable on the web.
# Do not put private data here.

# Initialize the array. Append to that array to add a throttle
$wmgThrottlingExceptions = array();

# $wmgThrottlingExceptions is an array of arrays of parameters:
#  'from'  => date/time to start raising account creation throttle
#  'to'    => date/time to stop
#
# Optional arguments can be added to set the value or restrict by client IP
# or project dbname. Options are:
#  'value'  => new value for $wgAccountCreationThrottle (default: 50)
#  'IP'     => client IP as given by $wgRequest->getIP() or array (default: any IP)
#  'range'  => alternatively, the client IP CIDR ranges or array (default: any range)
#  'dbname' => a $wgDBname or array of dbnames to compare to
#             (eg. enwiki, metawiki, frwikibooks, eswikiversity)
#             (default: any project)
## Add throttling definitions below.

$wmgThrottlingExceptions[] = array( // T110352 (long term)
	'from'   => '2015-08-29T00:00 +0:00',
	'to'     => '2016-02-28T00:00 +0:00',
	'IP'     => '218.248.16.20',
	'dbname' => 'tawiki',
	'value'  => 50
);
$wmgThrottlingExceptions[] = array( // T123161
	'from'   => '2016-01-12T15:00 +2:00',
	'to'     => '2016-01-12T21:00 +2:00',
	'range'  => array(
		'132.74.0.0/16',
		'132.75.0.0/16',
	),
	'dbname' => array( 'hewiki', 'commonswiki' ),
	'value'  => 50 // 30 expected participants
);
$wmgThrottlingExceptions[] = array( // T123351
	'from'   => '2016-01-14T17:00 +0:00',
	'to'     => '2016-01-15T22:30 +0:00',
	'IP'	 => array( '152.74.116.126', '152.74.116.104' ),
	'dbname' => array( 'eswiki', 'eswikivoyage' ),
	'value'  => 70 // 50 expected
);


## Add throttling definitions above.

/**
 * Helper to easily add a throttling request.
 */
$wgExtensionFunctions[] = function() {
	global $wmgThrottlingExceptions, $wgDBname, $wgRequest;

	foreach ( $wmgThrottlingExceptions as $options ) {
		# Validate entry, skip when it does not apply to our case

		# 1) skip when it does not apply to our database name

		if ( isset( $options['dbname'] ) && !in_array( $wgDBname, (array) $options['dbname'] ) ) {
			continue;
		}

		# 2) skip expired entries
		$inTimeWindow = time() >= strtotime( $options['from'] )
				&& time() <= strtotime( $options['to'] );

		if ( !$inTimeWindow ) {
			continue;
		}

		# 3) skip when throttle does not apply to the client IP
		$ip = $wgRequest->getIP();
		if ( isset( $options['IP'] ) && !in_array( $ip, (array) $options['IP'] ) ) {
			continue;
		}
		if ( isset ( $options['range'] ) && !IP::isInRanges( $ip, (array) $options['range'] ) ) {
			continue;
		}

		# Finally) set up the throttle value
		global $wgAccountCreationThrottle, $wgRateLimits;
		if ( isset( $options['value'] ) && is_numeric( $options['value'] ) ) {
			$wgAccountCreationThrottle = $options['value'];
		} else {
			$wgAccountCreationThrottle = 50; // Provide some sane default
		}
		$wgRateLimits['badcaptcha']['ip'] = array( 1000, 86400 );
		$wgRateLimits['badcaptcha']['newbie'] = array( 1000, 86400 );
		return; # No point in proceeding to another entry
	}
};
