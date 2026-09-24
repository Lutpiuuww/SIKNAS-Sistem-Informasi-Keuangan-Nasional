<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ministries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('budget_allocated', 20, 2); // Up to Trillions
            $table->timestamps();
        });

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // Provinsi, Kabupaten, Kota
            $table->decimal('tkdd_allocated', 20, 2); // Transfer Daerah
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->string('category'); // Pendapatan, Belanja Pusat, TKDD, dll
            $table->string('description');
            $table->decimal('amount', 20, 2);
            $table->string('type'); // IN, OUT
            $table->string('status'); // Success, Pending, Anomaly
            $table->foreignId('region_id')->nullable(); // Terikat pada region tertentu
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('ministries');
    }
};
