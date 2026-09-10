<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('results', function (Blueprint $table) {
        $table->foreignId('exam_id')->nullable()->constrained()->onDelete('cascade'); // Nullable pour éviter erreur
    });
}

    public function down()
    {
        Schema::table('results', function (Blueprint $table) {
            $table->dropForeign(['exam_id']);
            $table->dropColumn('exam_id');
        });
    }
};