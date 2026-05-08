<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'name',
        'description',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_uuid', 'uuid');
    }
}