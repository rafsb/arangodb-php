<?php
/**
 * ArangoDB PHP client testsuite
 * File: bootstrap-connection-close.php
 *
 * @package ArangoDBClient
 * @author  Frank Mayer
 */

namespace ArangoDBClient;

require __DIR__ . '/../autoload.php';

if (class_exists(\PHPUnit\Framework\TestCase::class)) {
    @class_alias(\PHPUnit\Framework\TestCase::class, 'PHPUnit_Framework_TestCase');
}

/* set up a trace function that will be called for each communication with the server */

function isCluster(Connection $connection)
{
    static $isCluster = null;

    if ($isCluster === null) {
        $adminHandler = new AdminHandler($connection);
        try {
            $role      = $adminHandler->getServerRole();
            $isCluster = ($role === 'COORDINATOR' || $role === 'DBSERVER');
        } catch (\Exception $e) {
            // maybe server version is too "old"
            $isCluster = false;
        }
    }

    return $isCluster;
}

function getConnectionOptions()
{
    $traceFunc = function ($type, $data) {
        print 'TRACE FOR ' . $type . PHP_EOL;
    };

    $passwd = getenv("ARANGO_ROOT_PASSWORD");
    if ($passwd === false) {
        $passwd = ""; // default root password
    }
    
    $host = getenv("ARANGO_HOST");
    if ($host === false) {
      $host = "localhost"; // default host
    }
    
    $port = getenv("ARANGO_PORT");
    if ($port === false) {
        $port = "8529"; // default port
    }

    return [
        ConnectionOptions::OPTION_ENDPOINT           => 'tcp://' . $host . ':' . $port,
        // endpoint to connect to
        ConnectionOptions::OPTION_CONNECTION         => 'Close',
        // can use either 'Close' (one-time connections) or 'Keep-Alive' (re-used connections)
        ConnectionOptions::OPTION_AUTH_TYPE          => 'Basic',
        // use basic authorization
        ConnectionOptions::OPTION_AUTH_USER          => 'root',
        // user for basic authorization
        ConnectionOptions::OPTION_AUTH_PASSWD        => $passwd,
        // password for basic authorization
        ConnectionOptions::OPTION_TIMEOUT            => 60,
        // timeout in seconds
        //ConnectionOptions::OPTION_TRACE       => $traceFunc,              // tracer function, can be used for debugging
        ConnectionOptions::OPTION_CREATE             => false,
        // do not create unknown collections automatically
        ConnectionOptions::OPTION_UPDATE_POLICY      => UpdatePolicy::LAST,
        // last update wins
        ConnectionOptions::OPTION_CHECK_UTF8_CONFORM => true
        // force UTF-8 checks for data
    ];
}


function getConnection()
{
    return new Connection(getConnectionOptions());
}
