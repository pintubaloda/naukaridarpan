<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_papers', function (Blueprint $table) {
            $table->string('tao_delivery_id')->nullable()->after('tao_test_id');
            $table->string('tao_sync_status')->default('pending')->after('tao_delivery_id');
            $table->timestamp('tao_synced_at')->nullable()->after('tao_sync_status');
            $table->text('tao_last_error')->nullable()->after('tao_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('exam_papers', function (Blueprint $table) {
            $table->dropColumn(['tao_delivery_id', 'tao_sync_status', 'tao_synced_at', 'tao_last_error']);
        });
    }
};
