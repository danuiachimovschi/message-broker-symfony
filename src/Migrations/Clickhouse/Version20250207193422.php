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
            <<<DANIEL
            CREATE TABLE events (
                id UUID PRIMARY KEY,               -- Unique identifier for the event
                event_name String,                 -- Name of the event (e.g., 'user_signup', 'order_created')
                event_data String,                 -- Data related to the event in a serialized format (e.g., JSON)
                created_at DateTime,               -- Timestamp of when the event was created
                updated_at DateTime DEFAULT now(), -- Timestamp of when the event was last updated
                user_id UUID,                      -- Optional: The ID of the user associated with the event (if applicable)
                event_type String,                 -- Optional: Type of the event (e.g., 'info', 'warning', 'error')
                source String,                     -- Optional: The source of the event (e.g., 'web', 'mobile', 'api')
                status String,                     -- Optional: Status of the event (e.g., 'pending', 'processed', 'failed')
                processed_at DateTime,             -- Optional: Timestamp of when the event was processed (if applicable)
                priority INT,                      -- Optional: Priority level of the event (e.g., 1-5)
                metadata String,                   -- Optional: Additional metadata related to the event (e.g., JSON)
                is_archived Boolean DEFAULT false, -- Optional: Flag to mark events as archived
                tags Array(String)                 -- Optional: Tags or categories for the event (e.g., ['user', 'login', 'success'])
            ) 
            ENGINE = MergeTree()
            ORDER BY (id, created_at)
            DANIEL,
        );
    }
}
