<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up():void{Schema::table('insumo_presentaciones',fn(Blueprint $table)=>$table->string('imagen')->nullable());}
    public function down():void{Schema::table('insumo_presentaciones',fn(Blueprint $table)=>$table->dropColumn('imagen'));}
};
