<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $t) {
            $t->string('tao_delivery_uri')->nullable()->after('exam_paper_id');
            $t->text('tao_launch_url')->nullable()->after('tao_delivery_uri');
            $t->json('tao_result')->nullable()->after('security_log');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $t) {
            $t->dropColumn(['tao_delivery_uri', 'tao_launch_url', 'tao_result']);
        });
    }
};
