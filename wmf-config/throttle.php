<?php

# WARNING: This file is publically viewable on the web.
# Do not put private data here.

## Add throttling definitions below,
# The helper functions takes an array of parameters:
#  'from'  => date/time to start raising account creation throttle
#  'to'    => date/time to stop
#
# Optional arguments can be added to set the value or restrict by client IP
# or project dbname. Options are:
#  'value'  => new value for $wgAccountCreationThrottle (default: 50)
#  'IP'     => client IP as given by wfGetIP() (default: any IP)
#  'dbname' => a $wgDBname or array of dbnames to compare to
#             (eg. enwiki, metawiki, frwikibooks, eswikiversity)
#             (default: any project)

# Initialize the array. Append to that array to add a throttle
$wmgThrottlingExceptions = array();

## Add throttling definition below

# bug 40263
$wmgThrottlingExceptions[] = array(
	'from'   => '2012-09-15T10:30 +0:00',
	'to'     => '2012-09-15T16:00 +0:00',
	'IP'     => '202.92.128.224',
	'dbname' => array( 'enwiki', 'tlwiki' ),
	'value'  => 50,
);
$wmgThrottlingExceptions[] = array(
        'from'   => '2012-09-15T10:30 +0:00',
        'to'     => '2012-09-15T16:00 +0:00',
        'IP'     => '202.92.130.3',
        'dbname' => array( 'enwiki', 'tlwiki' ),
        'value'  => 50,
);


## Add throttling defintion above.

# Will eventually raise value when MediaWiki is fully initialized:
$wgExtensionFunctions[] = 'efRaiseAccountCreationThrottle';

/**
 * Helper to easily add a throttling request.
 */
function efRaiseAccountCreationThrottle() {
	global $wmgThrottlingExceptions, $wgDBname;

	foreach ( $wmgThrottlingExceptions as $options ) {
		# Validate entry, skip when it does not apply to our case

		# 1) skip when it does not apply to our database name

		if( isset( $options['dbname'] ) ) {
			if ( is_array( $options['dbname'] ) ) {
				if ( !in_array( $wgDBname, $options['dbname'] ) ) {
					continue;
				}
			} elseif ( $wgDBname != $options['dbname'] ) {
				continue;
			}
		}

		# 2) skip expired entries
		$inTimeWindow = time() >= strtotime( $options['from'] )
				&& time() <= strtotime( $options['to'] );

		if( !$inTimeWindow ) {
			continue;
		}

		# @TODO: Make IP address accept array
		# 3) skip when throttle does not apply to the client IP
		if( isset( $options['IP'] ) && wfGetIP() != $throttle['IP'] ) {
			continue;
		}

		# Finally) set up the throttle value
		global $wgAccountCreationThrottle;
		if( isset( $throttle['value'] ) && is_numeric( $throttle['value'] ) ) {
			$wgAccountCreationThrottle = $throttle['value'];
		} else {
			$wgAccountCreationThrottle = 50; // Provide some sane default
		}
		return; # No point in proceeding to another entry
	}
}



// Added throttle for account creations on zh due to mass registration attack 2005-12-16
// might be useful elesewhere. --brion
// disabled temporarily due to tugela bug -- Tim

if ( false /*$lang == 'zh' || $lang == 'en'*/ ) {
	require( "$IP/extensions/UserThrottle/UserThrottle.php" );
	$wgGlobalAccountCreationThrottle = array(
/*
		'min_interval' => 30,   // Hard minimum time between creations (default 5)
		'soft_time'    => 300, // Timeout for rolling count
		'soft_limit'   => 5,  // 5 registrations in five minutes (default 10)
*/
		'min_interval' => 0,   // Hard minimum time between creations (default 5)
		'soft_time'    => 60, // Timeout for rolling count (default 5 minutes)
		'soft_limit'   => 2,  // 2 registrations in one minutes (default 10)
	);
}



