<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    use HasFactory;

    public $guarded = ['_token'];

    protected function name(): Attribute
    {
        return new Attribute(
            get: fn () => $this->first_name . ' '. $this->last_name,
        );
    }
}
