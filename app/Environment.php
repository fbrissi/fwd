<?php

namespace App;

use Dotenv\Dotenv;
use Dotenv\Environment\DotenvFactory;
use Dotenv\Exception\InvalidFileException;

class Environment
{
    protected static $keys = [
        'FWD_HTTP_PORT',
        'FWD_MYSQL_PORT',
        'FWD_ASUSER',
        'FWD_COMPOSE_EXEC_FLAGS',
        'FWD_SSH_KEY_PATH',
        'FWD_IMAGE_APP',
        'FWD_IMAGE_NODE',
        'FWD_IMAGE_CACHE',
        'FWD_IMAGE_DATABASE',
        'FWD_IMAGE_QA',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
    ];

    public function __construct(DotenvFactory $dotenvFactory)
    {
        $this->dotenvFactory = $dotenvFactory;
    }

    public static function getKeys(): array
    {
        return static::$keys;
    }

    public static function getValues(): array
    {
        return array_only(getenv(), static::getKeys());
    }

    public static function load(): void
    {
        static::loadEnv(getcwd(), '.fwd');
        static::loadEnv(getcwd(), '.env');
        static::loadEnv(base_path(), '.env.default');

        static::fixVariables();
    }

    protected static function loadEnv($path, $envFile): void
    {
        try {
            Dotenv::create(
                $path,
                $envFile
            )->safeLoad();
        } catch (InvalidFileException $e) {
            echo 'The environment file is invalid: '.$e->getMessage();
            die(1);
        }
    }

    protected static function fixVariables(): void
    {
        $envVariables = app(DotenvFactory::class)->create();

        $envVariables->set(
            'FWD_SSH_KEY_PATH',
            str_replace('$HOME', $_SERVER['HOME'], env('FWD_SSH_KEY_PATH'))
        );

        $envVariables->set(
            'FWD_ASUSER',
            str_replace('$UID', posix_geteuid(), env('FWD_ASUSER'))
        );
    }
}
