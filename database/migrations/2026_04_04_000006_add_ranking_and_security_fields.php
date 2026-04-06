<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->integer('rank_position')->nullable()->after('percentage');
            $table->decimal('percentile', 5, 2)->nullable()->after('rank_position');
            $table->json('anti_cheat_review')->nullable()->after('bookmarked_questions');
        });

        Schema::table('exam_papers', function (Blueprint $table) {
            $table->json('section_negative_rules')->nullable()->after('section_time_rules');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumn(['rank_position', 'percentile', 'anti_cheat_review']);
        });

        Schema::table('exam_papers', function (Blueprint $table) {
            $table->dropColumn('section_negative_rules');
        });
    }
};
