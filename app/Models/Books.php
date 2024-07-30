<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Books extends Model
{
    use HasFactory;

    protected $table = 'books';
    protected $primaryKey = 'id';

    protected $fillable = [
        'book_title',
        'book_publisher',
        'book_size',
        'book_stocks',
        'book_price',
        'book_status'
    ];
}
