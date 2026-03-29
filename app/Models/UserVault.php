<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVault extends Model
{
    protected $fillable = [
        'user_id',
        'kdf_salt',
        'encrypted_vault_key',
        'kdf_iterations',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
