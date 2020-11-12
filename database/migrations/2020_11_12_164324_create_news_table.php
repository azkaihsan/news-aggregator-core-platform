<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->string('author');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('url')->nullable();
            $table->text('urltoimage')->nullable();
            $table->text('content')->nullable();
            $table->dateTime('published_at', 0);
            $table->dateTime('load_at', 0);
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
