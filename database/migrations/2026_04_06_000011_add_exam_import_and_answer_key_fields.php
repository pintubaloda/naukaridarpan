<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_papers', function (Blueprint $table) {
            $table->string('source_url')->nullable()->after('source');
            $table->string('answer_key_pdf_url')->nullable()->after('original_file');
            $table->timestamp('answer_key_applied_at')->nullable()->after('answer_key_pdf_url');
            $table->text('answer_key_parse_log')->nullable()->after('answer_key_applied_at');
        });
    }

    public function down(): void
    {
        Schema::table('exam_papers', function (Blueprint $table) {
            $table->dropColumn([
                'source_url',
                'answer_key_pdf_url',
                'answer_key_applied_at',
                'answer_key_parse_log',
            ]);
        });
    }
};
