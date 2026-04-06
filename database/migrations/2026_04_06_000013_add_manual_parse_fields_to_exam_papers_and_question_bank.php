<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_papers', function (Blueprint $table) {
            $table->unsignedSmallInteger('exam_year')->nullable()->after('subject');
            $table->string('pdf_kind')->default('text')->after('source_url');
            $table->string('answer_key_mode')->default('same_pdf')->after('pdf_kind');
        });

        Schema::table('question_bank_items', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('category_id');
            $table->foreignId('source_exam_paper_id')->nullable()->after('bank_name')->constrained('exam_papers')->nullOnDelete();
            $table->string('source_exam_title')->nullable()->after('source_exam_paper_id');
            $table->unsignedSmallInteger('source_exam_year')->nullable()->after('source_exam_title');
            $table->unsignedInteger('source_question_serial')->nullable()->after('source_exam_year');
            $table->index(['bank_name', 'subject']);
            $table->unique(['source_exam_paper_id', 'source_question_serial'], 'question_bank_source_exam_serial_unique');
        });
    }

    public function down(): void
    {
        Schema::table('question_bank_items', function (Blueprint $table) {
            $table->dropUnique('question_bank_source_exam_serial_unique');
            $table->dropIndex(['bank_name', 'subject']);
            $table->dropConstrainedForeignId('source_exam_paper_id');
            $table->dropColumn([
                'bank_name',
                'source_exam_title',
                'source_exam_year',
                'source_question_serial',
            ]);
        });

        Schema::table('exam_papers', function (Blueprint $table) {
            $table->dropColumn(['exam_year', 'pdf_kind', 'answer_key_mode']);
        });
    }
};
