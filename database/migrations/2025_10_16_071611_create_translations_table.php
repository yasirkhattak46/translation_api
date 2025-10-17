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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('key', 191); // translation key (namespace.key)
            $table->foreignId('locale_id')->constrained('locales')->cascadeOnDelete();
            $table->text('content');
            $table->json('meta')->nullable(); // e.g. notes, context
            $table->timestamps();

            $table->unique(['key', 'locale_id']);
            $table->index(['locale_id', 'key']);
            $table->index('updated_at');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
