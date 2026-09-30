<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('topic')->index();
            $table->text('citation');
            $table->string('url')->nullable();
            $table->string('doi')->nullable();
            $table->string('publisher')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->text('relevance');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
