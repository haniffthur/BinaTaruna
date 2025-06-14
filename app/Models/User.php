<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username', 'nik','password', 'name', 'alamat', 'no_telp', 'jenis_kelamin', 'role'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // Gunakan username untuk login, bukan email
    public function username()
    {
        return 'username';
    }
    public function isAdmin()
{
    return $this->role === 'admin';
}

public function isPetugas()
{
    return $this->role === 'petugas';
}

}
