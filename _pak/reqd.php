<?php

// check required tools and php modules

if (!extension_loaded('zip')) {
	output('php-zip module not installed!');
}

if (!is_file('/usr/local/bin/hideg')) {
	output('/usr/local/bin/hideg is missing!');
}

if (!is_file('/usr/local/bin/fcl')) {
	output('/usr/local/bin/fcl is missing!');
}
