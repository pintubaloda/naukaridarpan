<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('email')->unique();
            $t->string('phone',15)->nullable(); $t->timestamp('email_verified_at')->nullable();
            $t->string('password'); $t->enum('role',['student','seller','admin'])->default('student');
            $t->string('avatar')->nullable(); $t->boolean('is_active')->default(true);
            $t->rememberToken(); $t->timestamps();
        });

        Schema::create('seller_profiles', function (Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('username')->unique(); $t->text('bio')->nullable();
            $t->string('qualification')->nullable(); $t->string('institution')->nullable();
            $t->json('subjects')->nullable(); $t->string('city')->nullable(); $t->string('state')->nullable();
            $t->string('website')->nullable(); $t->string('youtube_channel')->nullable(); $t->string('linkedin')->nullable();
            $t->decimal('rating',3,2)->default(0); $t->integer('total_reviews')->default(0);
            $t->integer('total_sales')->default(0); $t->decimal('total_earnings',12,2)->default(0);
            $t->decimal('wallet_balance',12,2)->default(0); $t->decimal('pending_balance',12,2)->default(0);
            $t->boolean('is_verified')->default(false); $t->timestamps();
        });

        Schema::create('categories', function (Blueprint $t) {
            $t->id(); $t->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $t->string('name'); $t->string('slug')->unique(); $t->string('icon')->nullable();
            $t->text('description')->nullable(); $t->integer('sort_order')->default(0);
            $t->boolean('is_active')->default(true); $t->timestamps();
        });

        Schema::create('exam_papers', function (Blueprint $t) {
            $t->id(); $t->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('category_id')->constrained('categories');
            $t->string('title'); $t->string('slug')->unique(); $t->text('description')->nullable();
            $t->string('language')->default('English');
            $t->enum('source',['upload','typed','scraped'])->default('upload');
            $t->string('original_file')->nullable();
            $t->enum('parse_status',['pending','processing','done','failed'])->default('pending');
            $t->text('parse_log')->nullable(); $t->string('tao_test_id')->nullable();
            $t->integer('total_questions')->default(0); $t->integer('duration_minutes')->default(60);
            $t->integer('max_marks')->default(100); $t->decimal('negative_marking',3,2)->default(0);
            $t->integer('max_retakes')->default(3);
            $t->enum('difficulty',['easy','medium','hard'])->default('medium');
            $t->json('question_types')->nullable(); $t->longText('questions_data')->nullable();
            $t->decimal('seller_price',8,2)->default(0); $t->decimal('platform_markup',8,2)->default(0);
            $t->decimal('student_price',8,2)->default(0); $t->boolean('is_free')->default(false);
            $t->string('thumbnail')->nullable(); $t->json('tags')->nullable();
            $t->enum('status',['draft','pending_review','approved','rejected','archived'])->default('draft');
            $t->text('rejection_reason')->nullable();
            $t->integer('total_purchases')->default(0); $t->integer('total_attempts')->default(0);
            $t->decimal('avg_score',5,2)->default(0); $t->timestamps();
        });

        Schema::create('purchases', function (Blueprint $t) {
            $t->id(); $t->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('exam_paper_id')->constrained()->cascadeOnDelete();
            $t->string('order_id')->unique(); $t->string('razorpay_payment_id')->nullable();
            $t->decimal('amount_paid',8,2); $t->decimal('platform_commission',8,2)->default(0);
            $t->decimal('seller_credit',8,2)->default(0);
            $t->enum('payment_status',['pending','paid','failed','refunded'])->default('pending');
            $t->integer('retakes_used')->default(0); $t->integer('retakes_allowed')->default(3);
            $t->timestamp('settlement_at')->nullable(); $t->boolean('is_settled')->default(false);
            $t->timestamps();
        });

        Schema::create('exam_attempts', function (Blueprint $t) {
            $t->id(); $t->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('exam_paper_id')->constrained()->cascadeOnDelete();
            $t->enum('status',['started','in_progress','submitted','evaluated'])->default('started');
            $t->timestamp('started_at')->nullable(); $t->timestamp('submitted_at')->nullable();
            $t->integer('time_taken_seconds')->nullable();
            $t->decimal('score',6,2)->nullable(); $t->decimal('percentage',5,2)->nullable();
            $t->integer('correct_answers')->default(0); $t->integer('wrong_answers')->default(0);
            $t->integer('unattempted')->default(0); $t->longText('answers')->nullable();
            $t->boolean('tab_switches')->default(false); $t->integer('tab_switch_count')->default(0);
            $t->text('security_log')->nullable(); $t->timestamps();
        });

        Schema::create('kyc_verifications', function (Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('pan_number',10)->nullable(); $t->string('pan_document')->nullable();
            $t->string('aadhaar_number',12)->nullable(); $t->string('aadhaar_document')->nullable();
            $t->string('bank_name')->nullable(); $t->string('account_number')->nullable();
            $t->string('ifsc_code',11)->nullable(); $t->string('bank_proof_document')->nullable();
            $t->enum('status',['pending','under_review','approved','rejected'])->default('pending');
            $t->text('rejection_reason')->nullable();
            $t->unsignedBigInteger('reviewed_by')->nullable(); $t->timestamp('reviewed_at')->nullable();
            $t->timestamps();
        });

        Schema::create('payout_requests', function (Blueprint $t) {
            $t->id(); $t->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $t->decimal('amount',10,2);
            $t->enum('status',['pending','processing','paid','failed','rejected'])->default('pending');
            $t->string('bank_name')->nullable(); $t->string('account_number')->nullable();
            $t->string('ifsc_code')->nullable(); $t->string('utr_number')->nullable();
            $t->text('admin_note')->nullable(); $t->timestamp('processed_at')->nullable(); $t->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('author_id')->nullable();
            $t->string('title'); $t->string('slug')->unique();
            $t->text('excerpt')->nullable(); $t->longText('content');
            $t->string('featured_image')->nullable(); $t->json('tags')->nullable();
            $t->string('category')->nullable(); $t->string('meta_title')->nullable();
            $t->string('meta_description')->nullable(); $t->boolean('is_ai_generated')->default(false);
            $t->enum('status',['draft','published','archived'])->default('draft');
            $t->timestamp('published_at')->nullable(); $t->integer('view_count')->default(0); $t->timestamps();
        });

        Schema::create('platform_settings', function (Blueprint $t) {
            $t->id(); $t->string('key')->unique(); $t->text('value')->nullable();
            $t->string('group')->default('general'); $t->timestamps();
        });

        Schema::create('professor_leads', function (Blueprint $t) {
            $t->id(); $t->string('name')->nullable(); $t->string('email')->nullable();
            $t->string('phone')->nullable(); $t->string('platform')->nullable();
            $t->string('institution')->nullable(); $t->string('subject')->nullable();
            $t->string('profile_url')->nullable(); $t->integer('subscriber_count')->default(0);
            $t->enum('outreach_status',['new','emailed','replied','onboarded','rejected'])->default('new');
            $t->integer('email_count')->default(0); $t->timestamp('last_emailed_at')->nullable(); $t->timestamps();
        });
    }

    public function down(): void {
        foreach(['professor_leads','platform_settings','blog_posts','payout_requests',
                 'kyc_verifications','exam_attempts','purchases','exam_papers',
                 'categories','seller_profiles','users'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
