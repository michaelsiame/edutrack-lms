<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $primaryKey = 'payment_method_id';
    public $incrementing = true;

    protected $fillable = [
        'method_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const UPDATED_AT = null;

    public function payments()
    {
        return $this->hasMany(Payment::class, 'payment_method_id');
    }
}
