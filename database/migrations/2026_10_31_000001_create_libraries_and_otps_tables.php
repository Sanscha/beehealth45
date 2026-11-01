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
        // 1. Add google_id and avatar to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('google_id');
            }
        });

        // 2. OTPs table for Email OTP verification
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('otp', 10);
            $table->string('type')->default('registration'); // user_register, manager_register, login
            $table->timestamp('expires_at');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        // 3. Libraries table for Library Owners / Managers
        Schema::create('libraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->text('address');
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 20)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('description')->nullable();
            $table->json('amenities')->nullable(); // ["WiFi", "AC", "Power Backup", "RO Water"]
            $table->integer('total_seats')->default(0);
            $table->decimal('registration_fee', 10, 2)->default(999.00);
            $table->enum('payment_status', ['unpaid', 'paid', 'waived'])->default('unpaid');
            $table->enum('status', ['pending_payment', 'active', 'suspended'])->default('pending_payment');
            $table->timestamps();
        });

        // 4. Update seats table to associate with library and allow nullable zone_id
        Schema::table('seats', function (Blueprint $table) {
            if (!Schema::hasColumn('seats', 'library_id')) {
                $table->foreignId('library_id')->nullable()->after('id')->constrained('libraries')->onDelete('cascade');
            }
        });

        // 5. Update payments table to associate with library and type
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'library_id')) {
                $table->foreignId('library_id')->nullable()->after('booking_id')->constrained('libraries')->onDelete('cascade');
            }
            if (!Schema::hasColumn('payments', 'type')) {
                $table->string('type')->default('seat_booking')->after('amount'); // library_registration, seat_booking, membership_plan
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'library_id')) {
                $table->dropForeign(['library_id']);
                $table->dropColumn('library_id');
            }
            if (Schema::hasColumn('payments', 'type')) {
                $table->dropColumn('type');
            }
        });

        Schema::table('seats', function (Blueprint $table) {
            if (Schema::hasColumn('seats', 'library_id')) {
                $table->dropForeign(['library_id']);
                $table->dropColumn('library_id');
            }
        });

        Schema::dropIfExists('libraries');
        Schema::dropIfExists('otps');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'google_id')) {
                $table->dropColumn('google_id');
            }
            if (Schema::hasColumn('users', 'avatar')) {
                $table->dropColumn('avatar');
            }
        });
    }
};
