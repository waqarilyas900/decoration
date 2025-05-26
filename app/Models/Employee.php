<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
  

    protected $fillable = [
        'is_delete'
    ];
    public function scopeSearchLike($query, $columns, $keyword)
    {
        return $query->where(function ($query) use ($columns, $keyword) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', '%' . $keyword . '%');
            }
        });
    }
}
