<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dateTime('results_publish_at')->nullable()->after('is_published');
            $table->boolean('show_vote_counts_public')->default(true)->after('results_publish_at');
        });
    }

    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropColumn(['results_publish_at', 'show_vote_counts_public']);
        });
    }
};
