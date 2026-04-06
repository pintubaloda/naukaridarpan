<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('question_bank_items', function (Blueprint $table) {
            $table->string('interaction_type')->nullable()->after('question_type');
            $table->string('qti_identifier')->nullable()->after('interaction_type');
            $table->json('advanced_metadata')->nullable()->after('correct_answer');
        });

        Schema::table('exam_papers', function (Blueprint $table) {
            $table->json('qti_metadata')->nullable()->after('exam_sections');
            $table->string('interoperability_profile')->nullable()->after('qti_metadata');
        });

        Schema::create('qti_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_paper_id')->nullable()->constrained('exam_papers')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->enum('direction', ['import', 'export']);
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->string('version')->default('QTI 2.2');
            $table->string('manifest_identifier')->nullable();
            $table->string('package_path')->nullable();
            $table->json('summary')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('assessment_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('integration_type')->default('lti');
            $table->string('endpoint_url');
            $table->string('auth_type')->default('bearer');
            $table->json('configuration')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_integrations');
        Schema::dropIfExists('qti_packages');

        Schema::table('exam_papers', function (Blueprint $table) {
            $table->dropColumn(['qti_metadata', 'interoperability_profile']);
        });

        Schema::table('question_bank_items', function (Blueprint $table) {
            $table->dropColumn(['interaction_type', 'qti_identifier', 'advanced_metadata']);
        });
    }
};
