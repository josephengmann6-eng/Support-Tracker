<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_entry_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            // Snapshots: later profile edits must never rewrite history.
            $table->string('user_name');
            $table->string('user_staff_id');
            $table->string('old_status');
            $table->string('new_status');
            $table->text('remark')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('new_status');
            $table->index('created_at');
            $table->index(['daily_entry_id', 'created_at']);
        });

        // Append-only is enforced in the database too, not just in the model.
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared("CREATE TRIGGER entry_updates_no_update BEFORE UPDATE ON entry_updates
                BEGIN SELECT RAISE(ABORT, 'entry_updates is append-only'); END");
            DB::unprepared("CREATE TRIGGER entry_updates_no_delete BEFORE DELETE ON entry_updates
                BEGIN SELECT RAISE(ABORT, 'entry_updates is append-only'); END");
        }
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS entry_updates_no_update');
        DB::unprepared('DROP TRIGGER IF EXISTS entry_updates_no_delete');
        Schema::dropIfExists('entry_updates');
    }
};
