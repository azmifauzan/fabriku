<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'attribute_name',
        'attribute_value',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
