<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 32)->unique();
            $table->string('title', 120)->nullable();
            $table->text('target_url');
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable()->index();
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        // One row per counted visit. No IP address, user agent or cookie is stored.
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_id')->constrained()->cascadeOnDelete();
            $table->timestamp('clicked_at')->useCurrent();
            $table->string('referrer_host', 190)->nullable();
            $table->string('device', 10)->default('desktop');
            $table->index(['link_id', 'clicked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clicks');
        Schema::dropIfExists('links');
    }
};
