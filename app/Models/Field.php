<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'type',
        'options',
        'validation_rules',
        'is_vault_protected',
    ];

    protected $casts = [
        'options' => 'array',
        'is_vault_protected' => 'boolean',
    ];

    public function dataTypes()
    {
        return $this->belongsToMany(DataType::class, 'data_type_field')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }
}
