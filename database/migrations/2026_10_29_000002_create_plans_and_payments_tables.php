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
        // Add status to users if not present
        if (! Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('status', ['active', 'blocked'])->default('active')->after('role');
            });
        }

        // Pricing / Membership Plans
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Morning Shift Pass, Full Day Pass, Monthly Regular
            $table->decimal('price', 10, 2);
            $table->integer('duration_days')->default(1);
            $table->enum('shift', ['morning', 'evening', 'full_day', 'all'])->default('morning');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Payments & Billing Transactions
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->foreignId('plan_id')->nullable()->constrained('plans')->onDelete('set null');
            $table->foreignId('collected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('INR');
            $table->enum('payment_method', ['cash', 'upi', 'online_razorpay', 'online_stripe', 'card'])->default('cash');
            $table->string('transaction_id')->nullable()->unique();
            $table->enum('status', ['completed', 'pending', 'failed', 'refunded'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('plans');

        if (Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
