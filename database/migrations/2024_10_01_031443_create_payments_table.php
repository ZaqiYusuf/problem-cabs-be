<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('id_customer');
            $table->string('id_imk');
            $table->date('pay_date');
            $table->string('amount_pay');
            $table->enum('status_pay', ['capture', 'pending', 'cancel', 'expire', 'refund', 'failure','paid']);
            $table->string('name_pay');
            $table->string('redirect_url');
            $table->string('order_id');
            $table->text('note_pay');
            $table->text('pay_method');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
