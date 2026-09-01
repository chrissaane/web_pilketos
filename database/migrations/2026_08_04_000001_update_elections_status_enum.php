<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->enum('status', ['Akan Datang', 'Sedang Berlangsung', 'Telah Berakhir'])
                  ->default('Akan Datang')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->enum('status', ['Sedang Berlangsung', 'Telah Berakhir'])
                  ->default('Sedang Berlangsung')
                  ->change();
        });
    }
};
