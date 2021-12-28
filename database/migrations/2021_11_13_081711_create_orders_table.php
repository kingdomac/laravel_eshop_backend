<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->nullable();
            $table->string('number')->index();
            $table->string('buyer_name');
            $table->string('buyer_email');
            $table->string('buyer_phone');
            $table->string('buyer_address');
            $table->tinyInteger('status')->default(1);
            $table->decimal('total', 8, 2);
            $table->tinyInteger('payment_method');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_product', function (Blueprint $table) {
            $table->foreignId('order_id')->index();
            $table->foreignId('product_id')->index();
            $table->decimal('price', 8, 2);
            $table->integer('quantity');

            $table->unique(['order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
