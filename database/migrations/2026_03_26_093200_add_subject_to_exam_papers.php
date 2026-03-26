<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_papers', function (Blueprint $t) {
            $t->string('subject')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('exam_papers', function (Blueprint $t) {
            $t->dropColumn('subject');
        });
    }
};
