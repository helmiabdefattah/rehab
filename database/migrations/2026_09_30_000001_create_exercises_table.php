<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('name_ar');
            $table->string('stage')->index();
            $table->string('section')->default('warmup')->index();
            $table->string('category')->index();
            $table->string('animation')->nullable();
            $table->json('focus');
            $table->json('target_muscles');
            $table->json('target_joints');
            $table->string('purpose');
            $table->text('why');
            $table->string('difficulty');
            $table->string('impact')->index();
            $table->unsignedTinyInteger('min_level')->default(1);
            $table->string('min_intensity')->default('light');
            $table->unsignedSmallInteger('seconds');
            $table->boolean('per_side')->default(false);
            $table->boolean('elastic')->default(false);
            $table->string('duration_label');
            $table->string('reps_label');
            $table->json('instructions');
            $table->json('mistakes');
            $table->json('safety');
            $table->json('activities');
            $table->json('equipment');
            $table->text('progression');
            $table->text('regression');
            $table->json('caution');
            $table->json('cues')->nullable();
            $table->json('sets')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('exercise_tag', function (Blueprint $table) {
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['exercise_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('exercises');
    }
};
