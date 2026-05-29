<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderQuoteStatusTable extends Migration
{
    public function up()
    {
        Schema::create('order_quote_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->string('history_status')->nullable();
            $table->date('expected_date')->nullable();
            $table->text('history_description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_quote_status');
    }
}
