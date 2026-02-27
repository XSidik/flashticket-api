<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Events table
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->dateTime('start_time');
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });

        // 2. Ticket categories table
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained();
            $table->string('name');
            $table->integer('total_quota');
            $table->integer('remaining_quota');
            $table->decimal('price', 15, 2);
            $table->timestamps();
        });

        // 3. Bookings table
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('ticket_category_id')->constrained();
            $table->string('booking_code')->unique();
            $table->enum('status', ['reserved', 'pending', 'paid', 'expired'])->default('reserved')->index();
            $table->dateTime('expires_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticketing_tables');
    }
};
