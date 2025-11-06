<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'author','price','stock' ,'book_category_id'];

    public function category()
    {
        return $this->belongsTo(BookCategory::class, 'book_category_id');
    }

    public function borrowRecords()
    {
        return $this->hasMany(BorrowRecord::class, 'book_id');
    }
}
