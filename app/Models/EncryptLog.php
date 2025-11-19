<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class EncryptLog extends Model
{
    use HasUlids;
    
    protected $table = 'encrypt_logs';
    protected $primaryKey = 'ulid';
    public $timestamps = false;

    protected $fillable = [
        'ulid',
        'text',
        'result',
        'mode',
    ];
}
