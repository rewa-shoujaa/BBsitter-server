<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parent_user extends Model
{
    protected $table = "parents";
    public function User()
    {
        return $this->hasOne(User::class , 'user_id');
    }

    public function address()
    {
        return $this->hasOne(Address::class , 'id');
    }
}
