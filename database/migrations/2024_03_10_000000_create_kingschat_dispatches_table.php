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
        Schema::create('kingschat_dispatches', function (Blueprint $table) {
            $table->id();
            $table->string('title');
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

        Schema::create('kingschat_dispatch_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_id')->constrained('kingschat_dispatches')->onDelete('cascade');
            $table->string('kc_user_id');
            $table->string('status')->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['status', 'delivered_at']);
            $table->index(['status', 'read_at']);
            $table->index(['dispatch_id', 'status']);
            $table->index(['kc_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kingschat_dispatch_recipients');
        Schema::dropIfExists('kingschat_dispatches');
    }
}; 