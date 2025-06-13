<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'title',
    ];
    public function user()
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }

    public function setUpdatedByAttribute($value)
    {
        $this->attributes['updated_by'] = preg_replace("/[^0-9]/", "", $value);
    }

    
}
