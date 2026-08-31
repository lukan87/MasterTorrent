<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('users', function (Blueprint $table) {

        $table->float('seeding_reputation')->default(0)->after('seedbonus');

        $table->unsignedTinyInteger('seeder_rank')
              ->default(0)
              ->after('seeding_reputation')
              ->comment('0=new,1=bronze,2=silver,3=gold,4=elite,5=legend');

    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {

        $table->dropColumn([
            'seeding_reputation',
            'seeder_rank'
        ]);

    });
}
};
