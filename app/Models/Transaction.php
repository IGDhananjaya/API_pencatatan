<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'nim',
        'type',
        'amount',
        'description',
        'date',
    ];

    // Define relationships if needed (e.g., for users)
    public function saldo()
    {
        return $this->belongsTo(Saldo::class, 'nim', 'nim');
    }
}