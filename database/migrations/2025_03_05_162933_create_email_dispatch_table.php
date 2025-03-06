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
        Schema::create('email_dispatches', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('message');
            $table->json('filters')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('error_log')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('sent_at');
        });

        Schema::create('email_dispatch_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_id')->constrained('email_dispatches')->onDelete('cascade');
            $table->string('email');
            $table->string('status')->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->text('error')->nullable();
            $table->string('unsubscribe_token', 32)->unique();
            $table->timestamps();

            $table->index(['status', 'delivered_at']);
            $table->index(['status', 'opened_at']);
            $table->index(['dispatch_id', 'status']);
            $table->index(['email', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_dispatch_recipients');
        Schema::dropIfExists('email_dispatches');
    }
}; 