<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saldo extends Model
{
    use HasFactory;

    protected $table = 'saldos'; // Nama tabel di database
    protected $primaryKey = 'nim'; // Kolom primary key
    public $incrementing = false; // Primary key bukan auto-incrementing
    protected $keyType = 'string'; // Tipe data primary key adalah string
    protected $fillable = ['nim', 'saldo']; // Kolom yang boleh diisi (mass assignment)

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'nim', 'nim');
    }
}
