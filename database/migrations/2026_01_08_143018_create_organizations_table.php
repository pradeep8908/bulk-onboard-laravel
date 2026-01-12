<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_id')->index();
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('contact_email')->nullable();

            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed'
            ])->default('pending')->index();

            $table->timestamp('processed_at')->nullable();
            $table->text('failed_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};

