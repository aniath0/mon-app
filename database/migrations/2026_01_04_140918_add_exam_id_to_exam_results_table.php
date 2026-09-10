<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('exam_results', function (Blueprint $table) {
        $table->foreignId('exam_id')->nullable()->after('result_id')->constrained()->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('exam_results', function (Blueprint $table) {
        $table->dropColumn('exam_id');
    });
}

};
