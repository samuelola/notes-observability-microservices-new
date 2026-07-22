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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index(); // indexed for search
            $table->text('content');

            /*
              Stores the ID of the user who owns the note
              Indexed for faster queries like:
              SELECT * FROM notes WHERE user_id = ?
            */
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            // composite index for faster user-based queries
            /*
               This creates a combined index on:
               user_id
               created_at
               It speeds up queries like:
               SELECT * FROM notes
               WHERE user_id = 5
               ORDER BY created_at DESC;
            */
            $table->index(['user_id', 'created_at']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
