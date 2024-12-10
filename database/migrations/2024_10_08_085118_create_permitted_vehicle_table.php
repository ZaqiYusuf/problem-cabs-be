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
        Schema::create('permitted_vehicle', function (Blueprint $table) {
            $table->id();
            $table->integer('id_imk');
            $table->string('plate_number');
            $table->string('no_lambung');
            $table->string('stnk');
            $table->string('driver_name');
            $table->string('sim');
            $table->integer('package_id');
            $table->integer('number_stiker');
            $table->integer('location_id');
            $table->string('cargo');
            $table->date('start_date')->nullable();
            $table->date('expired_at')->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permitted_vehicle');
    }
};
