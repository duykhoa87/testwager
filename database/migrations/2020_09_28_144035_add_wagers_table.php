<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wagers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('total_wager_value')->default(0);
            $table->integer('odds')->default(0);
            $table->integer('selling_percentage')->default(0);
            $table->decimal('selling_price');
            $table->decimal('current_selling_price')->nullable();
            $table->integer('percentage_sold')->nullable();
            $table->integer('amount_sold')->nullable();
            $table->timestamp('placed_at');
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
        Schema::dropIfExists('wagers');
    }
}
