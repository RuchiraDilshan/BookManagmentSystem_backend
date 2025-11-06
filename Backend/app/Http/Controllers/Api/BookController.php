<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;;
use Illuminate\Http\Request;


class BookController extends Controller
{
    //book list with category
    public function index()
    {

        $categoryId = request()->query('category_id');
        $query = Book::with('category');
        if($categoryId) {
            $query->where('book_category_id', $categoryId);
        }

        //to stock label
       $books = $query->get()->map(function($b) {

            $b->stock_label = $b->stock <= 0 ? 'Out of stock' : (string)$b->stock;
            return $b;
        });

        return response()->json($books);

    }

        //to store new book

        public function store(StoreBookRequest $request)
        {
            $data = $request->validated();
            $book = Book::create($data);
            return response()->json($book, 201);
        }


        // to show book detail

        public function show($id)
        {
            $book = Book::with('category')->findOrFail($id);
            return response()->json($book);
        }


        // to update book

        public function update(UpdateBookRequest $request, $id)
        {
            $data = $request->validated();
            $book = Book::findOrFail($id);
            $book->update($data);
            return response()->json($book);
        }


        //to delete book

        public function destroy($id)
        {
            $book = Book::findOrFail($id);
            $book->delete();
            return response()->json(null, 204);
        }
    }
