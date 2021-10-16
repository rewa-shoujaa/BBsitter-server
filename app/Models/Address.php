<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Address extends Model
{
    protected $table = "addresses";
    public function parent()
    {
        return $this->hasOne(Parent_user::class , 'address_id');
    }

    public function babysitter()
    {
        return $this->hasOne(Babysitter::class);
    }
}
