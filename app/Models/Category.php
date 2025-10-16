<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use App\Traits\Auditable;

class Category extends Model
{
    use HasFactory;
    // use Auditable;

    protected $table = 'category';

    protected $fillable = [
        'name',
        'description',
        'color',
        'type',
        'active',
        'created_by'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
