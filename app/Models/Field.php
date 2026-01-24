<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $fillable = [
        'name',
        'label',
        'type',
        'options',
        'validation_rules',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function dataTypes()
    {
        return $this->belongsToMany(DataType::class, 'data_type_field')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }
}
