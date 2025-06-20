<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAssignmentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'assignment_id',
        'employee_id',
        'title',
        'updated_by',
        'section',
        'garments_assigned',
        'status',
    ];

     public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

}
