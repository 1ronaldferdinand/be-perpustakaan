<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrows extends Model
{
    use HasFactory;
    
    protected $table = 'borrows';
    protected $primaryKey = 'id';

    protected $fillable = [
        'book_id',
        'customer_id',
        'borrow_start',
        'borrow_end',
        'borrow_status'
    ];

    public function book()
    {
        return $this->belongsTo(Books::class, 'book_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }
}
