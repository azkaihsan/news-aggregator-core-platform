<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyNewsCountryColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('countries', function (Blueprint $table) {
            //Make Char into 5 characters
            $table->char('code',5)->change();
            //Make Everything Else Nullable
            $table->string('full_name')->nullable()->change();
            $table->char('iso3',3)->nullable()->change();
            $table->char('number',3)->nullable()->change();
            $table->char('continent_code',2)->nullable()->change();   
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('countries', function (Blueprint $table) {
            //Make Char into 2 characters
            $table->char('code',2)->change();
            //Make Everything Else Not Nullable
            $table->string('full_name')->nullable(false)->change();
            $table->char('iso3',3)->nullable(false)->change();
            $table->char('number',3)->nullable(false)->change();
            $table->char('continent_code',2)->nullable(false)->change();   
        });
    } 
}
