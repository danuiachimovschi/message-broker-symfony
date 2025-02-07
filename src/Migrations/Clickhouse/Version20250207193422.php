<?php

declare(strict_types=1);

namespace App\Migrations\Clickhouse;

use ClickHouseDB\Client;
use ClickhouseMigrations\AbstractClickhouseMigration;

final class Version20250207193422 extends AbstractClickhouseMigration
{
    public function up(Client $client): void
    {
        $client->write(
            <<<CLICKHOUSE
            CREATE TABLE IF NOT EXISTS events
            (
                id UUID,
                event_name String,
                event_data String,
                created_at DateTime
            ) 
            ENGINE = MergeTree()
            ORDER BY (created_at)
            CLICKHOUSE,
        );
    }
}
