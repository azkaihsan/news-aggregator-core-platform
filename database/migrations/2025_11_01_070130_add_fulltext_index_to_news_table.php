<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFulltextIndexToNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create a tsvector column (optional, can also generate on the fly)
        DB::statement("
            ALTER TABLE news
            ADD COLUMN searchable tsvector
            GENERATED ALWAYS AS (
                to_tsvector('english', coalesce(title, '') || ' ' || coalesce(description, '') || ' ' || coalesce(content, '') || ' ' || coalesce(author, ''))
            ) STORED
        ");

        // Create a GIN index on the tsvector
        DB::statement("CREATE INDEX news_searchable_index ON news USING GIN (searchable)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS news_searchable_index");
        DB::statement("ALTER TABLE news DROP COLUMN IF EXISTS searchable");
    }
}
