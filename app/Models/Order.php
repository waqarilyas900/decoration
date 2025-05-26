<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'need_sewing',
        'need_embroidery',
        'need_imprinting',
        'current_location',
        'current_location',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function logs()
    {
       return $this->hasMany(OrderLog::class);
    }
    public function track()
    {
       return $this->hasMany(OrderTrack::class, 'order_id');
    }
   
    public function removed()
    {
        return $this->hasOne(OrderLog::class, 'order_id')->where('removed_by', '!=', null);
    }
    use HasFactory;

    public function scopeSearchLike($query, $columns, $keyword)
    {
        return $query->where(function ($query) use ($columns, $keyword) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', '%' . $keyword . '%');
            }
        });
    }
}
