<?php

/**
 * Script for the migration of the PHP sessions
 */

/**
 * Include session storage managers
 */
require_once 'FileSessionManager.php';
require_once 'MemcacheSessionManager.php';


/**
 * Params of the session managers
 */
$params = array(
    'session_managers' => array(
        'files' => array(
            'class_name' => 'FileSessionManager',
            'prefix' => 'sess_',
            'params' => array('path' => '/var/lib/php5/'),
        ),
        'memcache' => array(
            'class_name' => 'MemcacheSessionManager',
            'prefix' => 'memc.sess.key.',
            'params' => array(
                'host' => 'localhost', 
                'port' => 11211,
            )
        ),
        'redis' => array(
            'class_name' => 'RedisSessionManager',
            'prefix' => 'PHPREDIS_SESSION:',
            'params' => array(
                'host' => 'localhost',
                'port' => '',
            )
        ),
    )
);


/**
 * Get list of the supported session storages
 * @return array
 */
function get_supported_storages()
{
    global $params; 
    return array_keys($params['session_managers']);
}


/**
 * Print help message for user
 */
function echo_usage()
{
    global $argv;
    $s = "Usage: php " . $argv[0] . " --from=<source> --to=<destination>\n\n";
    $s .= "Supported storages: " . implode(', ', get_supported_storages());
    echo $s;
}


/**
 * Move php sessions from one storage to other
 * @param  SessionManager $src source
 * @param  SessionManager $dest destination
 * @param  boolean $verbose show verbose output
 * @return integer count of the moved sessions
 */
function move_sessions(BaseSessionManager $src, BaseSessionManager $dest, $verbose = true)
{
    $count = 0;
    $keys = $src->getAllKeys();
    if ($verbose) {
        echo "Moving sessions...\n\n";
    }
    foreach ($keys as $k) {
        $session_contents = $src->get($k);
        $dest->set($k, $session_contents);
        $count++;
        if ($verbose) {
            echo $k . "\n";
        }
    }
    if ($verbose) {
        echo "\nDone.\n";
        echo "Session moved: $count\n";
    }
    return $count;
}


/**
 * Run session migration process
 */
function main()
{
    global $params;
    $longopts = array('from:', 'to:');
    $opts = getopt('', $longopts);
    $supported_storages = get_supported_storages();

    if (empty($opts['from']) || empty($opts['to'])) {
        echo_usage();
        exit(1);
    }
    if (!in_array($opts['from'], $supported_storages)) {
        echo_usage();
        exit(1);
    }
    if (!in_array($opts['to'], $supported_storages)) {
        echo_usage();
        exit(1);
    }
    if ($opts['to'] === $opts['from']) {
        echo "Source cannot be destination\n";
        exit(1);
    }

    $managers = $params['session_managers'];

    $src_params =  $managers[$opts['from']]['params'];
    $src_cls = $managers[$opts['from']]['class_name'];
    $src = new $src_cls($managers[$opts['from']]['prefix'], $src_params);

    $dest_params =  $managers[$opts['to']]['params'];
    $dest_cls = $managers[$opts['to']]['class_name'];

    $dest = new $dest_cls($managers[$opts['to']]['prefix'], $dest_params);

    move_sessions($src, $dest);
}

ini_set('register_argc_argv', 1);
main();
