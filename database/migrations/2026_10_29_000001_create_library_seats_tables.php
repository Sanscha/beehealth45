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
        // Seat Zones / Floors
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Silent Zone, Ground Floor, AC Hall
            $table->string('code')->unique(); // e.g., G-ZONE, S-ZONE
            $table->timestamps();
        });

        // Seats Master
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('cascade');
            $table->string('seat_number'); // e.g., A-101, A-102
            $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
            $table->timestamps();
        });

        // Bookings & Active Passes
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seat_id')->constrained('seats')->onDelete('cascade');
            $table->string('pass_code')->unique(); // Unique entry token/QR code
            $table->enum('shift', ['morning', 'evening', 'full_day']);
            $table->date('booking_date');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('seats');
        Schema::dropIfExists('zones');
    }
};
