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
        Schema::create('process_imk', function (Blueprint $table) {
            $table->id();
            $table->string('imk_number');
            $table->string('document_number');
            $table->string('customer')->nullable();
            $table->integer('tenant_id');
            $table->string('total_cost')->nullable();
            $table->date('registration_date');
            $table->enum('item', ['tenant', 'nontenant']);
            $table->boolean('status_imk')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_imk');
    }
};
