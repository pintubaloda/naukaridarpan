<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('automation_sources', function (Blueprint $table) {
            $table->id();
            $table->string('subject')->nullable();
            $table->string('name');
            $table->string('source_type')->default('rss');
            $table->string('site_kind')->nullable();
            $table->string('base_url')->nullable();
            $table->string('rss_url')->nullable();
            $table->string('discovery_query')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_item_at')->nullable();
            $table->timestamps();
        });

        Schema::create('automation_run_logs', function (Blueprint $table) {
            $table->id();
            $table->string('workflow_name');
            $table->string('run_type')->nullable();
            $table->string('subject')->nullable();
            $table->string('status')->default('processed');
            $table->json('payload_summary')->nullable();
            $table->text('message')->nullable();
            $table->unsignedInteger('processed_count')->default(0);
            $table->timestamps();
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('category');
            $table->string('source_name')->nullable()->after('subject');
            $table->string('source_url')->nullable()->after('source_name');
            $table->string('import_hash')->nullable()->unique()->after('source_url');
            $table->string('import_channel')->nullable()->after('import_hash');
        });

        Schema::table('professor_leads', function (Blueprint $table) {
            $table->string('department')->nullable()->after('subject');
            $table->string('designation')->nullable()->after('department');
            $table->string('source_name')->nullable()->after('profile_url');
            $table->string('source_url')->nullable()->after('source_name');
            $table->string('lead_hash')->nullable()->unique()->after('source_url');
            $table->text('notes')->nullable()->after('lead_hash');
        });
    }

    public function down(): void
    {
        Schema::table('professor_leads', function (Blueprint $table) {
            $table->dropColumn(['department', 'designation', 'source_name', 'source_url', 'lead_hash', 'notes']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropUnique(['import_hash']);
            $table->dropColumn(['subject', 'source_name', 'source_url', 'import_hash', 'import_channel']);
        });

        Schema::dropIfExists('automation_run_logs');
        Schema::dropIfExists('automation_sources');
    }
};
