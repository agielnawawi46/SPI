<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite does not support ALTER COLUMN for enums directly.
        // We recreate the column by adding new values via a workaround:
        // Since SQLite stores enums as CHECK constraints, we need to
        // rebuild the table. The easiest safe approach for SQLite is
        // to drop and recreate the check constraint by rebuilding.
        //
        // However, Laravel's SQLite driver doesn't support ->change() on enums well.
        // The cleanest solution: change the column to a plain string (no check constraint),
        // which is semantically equivalent and more flexible.
        DB::statement('PRAGMA foreign_keys=off;');

        DB::statement("CREATE TABLE spi_notifications_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            user_id INTEGER NOT NULL,
            title VARCHAR NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR CHECK(type IN ('deadline','approval','info','warning','success','revision','error')) NOT NULL DEFAULT 'info',
            link VARCHAR NULL,
            notifiable_type VARCHAR NULL,
            notifiable_id INTEGER UNSIGNED NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            read_at DATETIME NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");

        DB::statement('INSERT INTO spi_notifications_new SELECT * FROM spi_notifications');
        DB::statement('DROP TABLE spi_notifications');
        DB::statement('ALTER TABLE spi_notifications_new RENAME TO spi_notifications');

        DB::statement('CREATE INDEX spi_notifications_user_id_is_read_index ON spi_notifications (user_id, is_read)');
        DB::statement('CREATE INDEX spi_notifications_notifiable_type_notifiable_id_index ON spi_notifications (notifiable_type, notifiable_id)');

        DB::statement('PRAGMA foreign_keys=on;');
    }

    public function down(): void
    {
        DB::statement('PRAGMA foreign_keys=off;');

        DB::statement("CREATE TABLE spi_notifications_old (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            user_id INTEGER NOT NULL,
            title VARCHAR NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR CHECK(type IN ('deadline','approval','info','warning')) NOT NULL DEFAULT 'info',
            link VARCHAR NULL,
            notifiable_type VARCHAR NULL,
            notifiable_id INTEGER UNSIGNED NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            read_at DATETIME NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");

        DB::statement('INSERT INTO spi_notifications_old SELECT * FROM spi_notifications WHERE type IN (\'deadline\',\'approval\',\'info\',\'warning\')');
        DB::statement('DROP TABLE spi_notifications');
        DB::statement('ALTER TABLE spi_notifications_old RENAME TO spi_notifications');

        DB::statement('CREATE INDEX spi_notifications_user_id_is_read_index ON spi_notifications (user_id, is_read)');
        DB::statement('CREATE INDEX spi_notifications_notifiable_type_notifiable_id_index ON spi_notifications (notifiable_type, notifiable_id)');

        DB::statement('PRAGMA foreign_keys=on;');
    }
};
