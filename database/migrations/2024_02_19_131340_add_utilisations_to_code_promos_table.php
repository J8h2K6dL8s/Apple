<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('code_promos', function (Blueprint $table) {
            $table->integer('nombreUtilise')->default(0)->after('nombreUtilisation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('code_promos', function (Blueprint $table) {
            $table->dropColumn('nombreUtilise');
        });

    }
};
