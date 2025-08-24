<?php

namespace App\Models;

use App\Enums\TaskPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'category_id',
        'position',
        'priority',
        'completed',
        'completion_date',
        'status'
    ];

    protected $casts = [
        'category_id' => 'integer',
        'position' => 'integer',
        'completion_date' => 'datetime',
        'priority' => TaskPriority::class,
        'status' => 'boolean'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($task) {
            if (is_null($task->position)) {
                $task->position = static::where('category_id', $task->category_id)->max('position') + 1;
            }
        });
    }
}
