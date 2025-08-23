<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllocateShareHistory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function userShare(): BelongsTo
    {
        return $this->belongsTo(UserShare::class);
    }
}
