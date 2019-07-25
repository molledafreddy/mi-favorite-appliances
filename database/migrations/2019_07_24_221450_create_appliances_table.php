<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppliancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appliances', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->string('title');
            $table->string('price');
            $table->string('price_previus')->nullable();
            $table->boolean('interest')->default(0);
            $table->boolean('status_warranty')->default(0);
            $table->mediumText('links')->nullable();
            $table->mediumText('description')->nullable();
            $table->string('image');
            $table->string('logo');
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
        Schema::dropIfExists('appliances');
    }
}
