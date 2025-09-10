<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NewsRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('news', function (Blueprint $table) {
            // Relationship
            $table->foreign('source_id')
                ->references('id')->on('news_sources')
                ->onDelete('cascade');
            $table->foreign('category_id')
                ->references('id')->on('news_categories')
                ->onDelete('cascade');
            $table->foreign('country_id')
                ->references('id')->on('countries')
                ->onDelete('cascade');    
        });    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('news', function (Blueprint $table) {
            // Relationship
            $table->dropForeign(['source_id']);
            $table->dropForeign(['category_id']);
        });
    }
}
