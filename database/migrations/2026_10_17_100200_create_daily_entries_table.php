<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->string('status')->default('pending'); // pending | done
            $table->text('remark')->nullable();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['activity_id', 'date']);
            $table->index('date');
            $table->index('status');
            $table->index(['status', 'date']);
            $table->index('last_updated_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_entries');
    }
};
