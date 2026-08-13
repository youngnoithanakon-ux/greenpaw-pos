<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;

#[Fillable(['username', 'fullname', 'role', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * `getAuthIdentifierName` ต้องคืน 'id' เพื่อให้ Auth::id() และ session เก็บ integer
     * การ login ด้วย username ทำได้โดย Auth::attempt(['username'=>...,'password'=>...])
     * ซึ่ง EloquentUserProvider จะ query WHERE username = ? โดยอัตโนมัติจาก key ที่ส่งมา
     */

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship: A user can perform many sales.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Relationship: A user can perform many stock logs.
     */
    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }
}
