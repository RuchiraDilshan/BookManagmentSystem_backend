<?php

namespace App\Http\Controllers\Api;

use App\Models\BookCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    //category list
    public function index()
    {
        $categories = BookCategory::all();
        return response()->json($categories);
    }

}
