<?php

namespace App\Models;

use App\Filament\TreePlugin\Concern\ModelTree;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use ModelTree;

    public function parent()
    {
        return $this->belongsTo(Node::class, 'parent_id');
    }

    public static function defaultParentKey()
    {
        return null;
    }

    protected $fillable = [
        'parent_id',
        'data_type_id',
        'title',
        'data',
        'order',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function dataType()
    {
        return $this->belongsTo(DataType::class);
    }
}
