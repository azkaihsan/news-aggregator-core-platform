<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->integer('source_id');
            $table->integer('category_id');
            $table->integer('country_id');
            $table->text('title');
            $table->text('author')->nullable();
            $table->text('description')->nullable();
            $table->text('url')->nullable();
            $table->text('urltoimage')->nullable();
            $table->text('content')->nullable();
            $table->dateTime('published_at', 0);
            $table->dateTime('load_at', 0)->default(new Expression('CURRENT_TIMESTAMP'));
            $table->timestamps();                
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news');
    }
}
