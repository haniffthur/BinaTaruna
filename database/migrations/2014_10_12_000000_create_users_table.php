<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('nik', 100);
            $table->string('password');
            $table->string('name', 100);
            $table->string('alamat', 500);
            $table->string('no_telp',50);
            $table->enum('jenis_kelamin',['laki-laki','perempuan']);
            $table->enum('role', ['admin', 'petugas']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}

