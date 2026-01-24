<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Field;
use App\Models\Node;

class DataType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'default_child_type_id',
    ];

    public function fields()
    {
        return $this->belongsToMany(Field::class, 'data_type_field')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function defaultChildType()
    {
        return $this->belongsTo(DataType::class, 'default_child_type_id');
    }

    public function nodes()
    {
        return $this->hasMany(Node::class);
    }
}
