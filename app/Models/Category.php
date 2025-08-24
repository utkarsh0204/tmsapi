<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, "category_id", "id")->orderBy('position');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (is_null($category->position)) {
                $category->position = static::max('position') + 1;
            }
        });
    }
}
