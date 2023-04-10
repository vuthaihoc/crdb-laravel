<?php

namespace YlsIdeas\CockroachDb;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Support\ConfigurationUrlParser;
use Illuminate\Support\ServiceProvider;

class CockroachDbServiceProvider extends ServiceProvider
{
    public function register()
    {

        $this->app->extend(DatabaseManager::class, function (DatabaseManager $manager) {
            ConfigurationUrlParser::addDriverAlias('cockroachdb', 'crdb');

            Connection::resolverFor('crdb', function ($connection, $database, $prefix, $config) use ($manager) {
                $connector = new CockroachDbConnector();

                $hosts = Arr::wrap($config['host']);
                foreach (Arr::shuffle($hosts) as $host) {
                    $config['host'] = $host;
                    try {
                        $pdo = $connector->connect($config);
                        break;
                    } catch (PDOException $e) {
                        continue;
                    }
                }

                return new CockroachDbConnection($pdo, $database, $prefix, $config);
            });

            $this->app->bind('db.connector.crdb', function(){
                return new CockroachDbConnector();
            });

            return $manager;
        });
    }
}
