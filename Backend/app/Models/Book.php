<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Modal
{
    use HasFactory;

    protected $fillable = ['title', 'author', 'book_category_id'];

    public function category()
    {
        return $this->belongsTo(BookCategory::class, 'book_category_id');
    }
}
