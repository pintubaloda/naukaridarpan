<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('automation_sources', function (Blueprint $table) {
            $table->string('listing_page_url')->nullable()->after('rss_url');
            $table->string('answer_key_listing_url')->nullable()->after('listing_page_url');
            $table->string('pdf_kind')->default('text')->after('answer_key_listing_url');
            $table->string('answer_key_mode')->default('same_pdf')->after('pdf_kind');
        });
    }

    public function down(): void
    {
        Schema::table('automation_sources', function (Blueprint $table) {
            $table->dropColumn([
                'listing_page_url',
                'answer_key_listing_url',
                'pdf_kind',
                'answer_key_mode',
            ]);
        });
    }
};
