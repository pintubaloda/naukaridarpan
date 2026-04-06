<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->json('bookmarked_questions')->nullable()->after('question_timings');
        });

        Schema::table('exam_papers', function (Blueprint $table) {
            $table->json('section_time_rules')->nullable()->after('question_types');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumn('bookmarked_questions');
        });

        Schema::table('exam_papers', function (Blueprint $table) {
            $table->dropColumn('section_time_rules');
        });
    }
};
