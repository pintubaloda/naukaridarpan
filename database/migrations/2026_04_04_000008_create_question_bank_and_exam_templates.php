<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('question_bank_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('subject')->nullable();
            $table->string('section')->nullable();
            $table->string('topic')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->string('question_type')->default('mcq');
            $table->longText('question_text');
            $table->json('options')->nullable();
            $table->json('correct_answer')->nullable();
            $table->longText('explanation')->nullable();
            $table->decimal('marks', 6, 2)->default(1);
            $table->decimal('negative_marking', 6, 2)->default(0);
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category_id', 'difficulty']);
            $table->index(['question_type', 'subject']);
        });

        Schema::create('exam_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->default(60);
            $table->decimal('default_negative_marking', 6, 2)->default(0);
            $table->json('sections')->nullable();
            $table->json('template_data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_templates');
        Schema::dropIfExists('question_bank_items');
    }
};
