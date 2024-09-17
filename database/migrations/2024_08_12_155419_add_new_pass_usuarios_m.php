<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'sqlsrv2'; //conexion de la base de datos nueva.


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usuarioss', function (Blueprint $table) {
            $table->integer('change_pass')->default(0)->nullable();
        });

        DB::transaction(function () {
            DB::table('usuarioss')->update(['change_pass' => 0]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usuarioss', function (Blueprint $table) {
            $table->dropColumn('change_pass');
        });
    }
};
