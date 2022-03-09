<?php

namespace App\Helpers;

use PDO;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Connection;

class DBHelper
{
    /**
     * Get connection.
     *
     * @param  string  $name
     * @return \Illuminate\Database\Connection
     */
    public static function connection($name = null): Connection
    {
        return DB::connection($name);
    }

    /**
     * Get the version of DB engine.
     *
     * @return string|null
     */
    public static function version(): ?string
    {
        try {
            return static::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Test if db version if greater than $version param.
     *
     * @param  string  $version
     * @return bool
     */
    public static function testVersion($version)
    {
        return version_compare(static::version(), $version) >= 0;
    }

    /**
     * Get list of tables on this instance.
     *
     * @return array
     */
    public static function getTables()
    {
	$db_name = static::connection()->getDatabaseName();
	$table_prefix = '%'.static::connection()->getTablePrefix().'%';

        if (DB::connection()->getDriverName() == 'pgsql') {
            return DB::select('SELECT table_name
                    FROM information_schema.tables
                    WHERE table_catalog = :table_catalog
                    AND table_schema = :table_schema
                    AND table_name LIKE :table_prefix', [
                'table_catalog' => $db_name,
                'table_schema' => 'public',
                'table_prefix' => $table_prefix,
            ]);
	}

        return DB::select('SELECT table_name as `table_name`
                FROM information_schema.tables
                WHERE table_schema = :table_schema
                AND table_name LIKE :table_prefix', [
            'table_schema' => $db_name,
            'table_prefix' => $table_prefix,
        ]);
    }

    public static function getTable($name)
    {
        $quote_char = static::getQuoteChar();
        return $quote_char.static::connection()->getTablePrefix().$name.$quote_char;
    }

    public static function getTableColumn($table, $column)
    {
        $quote_char = static::getQuoteChar();
        return static::getTable($table).'.'.$quote_char.$column.$quote_char;
    }

    public static function getQuoteChar()
    {
        return DB::connection()->getDriverName() == 'pgsql' ? '"' : '`';
    }
}
