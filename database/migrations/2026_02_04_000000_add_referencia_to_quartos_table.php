<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferenciaToQuartosTable extends Migration
{
    public function up()
    {
        Schema::table('quartos', function (Blueprint $table) {
            $table->string('referencia')->nullable()->after('numero');
        });
    }

    public function down()
    {
        Schema::table('quartos', function (Blueprint $table) {
            $table->dropColumn('referencia');
        });
    }
}

