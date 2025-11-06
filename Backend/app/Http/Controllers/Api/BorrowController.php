<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\BorrowRecord;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class BorrowController extends Controller
{
    //to borrow a book
    public function borrow(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'book_id' => ['required', 'exists:books,id'],
        ]);

        return DB::transaction(function () use ($request) {
            $book = Book::findOrFail($request->book_id);


            // to check book avilable
            if ($book->stock <= 0) {
                return response()->json(['message' => 'Book is out of stock'], 400);
            }

            // to check user already borrowed the book and not returned yet
            $existingBorrow = BorrowRecord::where('user_id', $request->user_id)
    ->where('book_id', $request->book_id)
    ->where('type', 'borrow')
    ->latest()
    ->first();

            if ($existingBorrow) {

                $latestReturn = BorrowRecord::where('user_id', $request->user_id)
        ->where('book_id', $request->book_id)
        ->where('type', 'return')
        ->where('event_date', '>=', $existingBorrow->event_date)
        ->first();

              if (!$latestReturn) {
        return response()->json(['message' => 'User has already borrowed this book and not returned yet'], 400);
    }
            }

            // to decrease stock

            $book->stock = $book->stock - 1;
            $book->save();


            // to create borrow record

            BorrowRecord::create([
                'user_id' => $request->user_id,
                'book_id' => $request->book_id,
                'type' => 'borrow', // ✅ Use your actual columns
    'event_date' => now(),
    'notes' => 'Book borrowed'
            ]);

            return response()->json(['message' => 'Book borrowed successfully'], 200);
        });

    }

    // to return a book
    public function returnBook(Request $request,$borrowRecordId=null)
    {
        if($borrowRecordId){
            return DB::transaction(function () use ($borrowRecordId) {

            $record = BorrowRecord::lockForUpdate()->findOrFail($borrowRecordId);

            if ($record->type !== 'borrow') {
    return response()->json(['message' => 'This is not a borrow record'], 400);
}
$existingReturn = BorrowRecord::where('user_id', $record->user_id)
    ->where('book_id', $record->book_id)
    ->where('type', 'return')
    ->first();

if ($existingReturn) {
    return response()->json(['message' => 'Book has already been returned'], 400);
}




            $book = Book::lockForUpdate()->findOrFail($record->book_id);
            $book->stock = $book->stock + 1;
            $book->save();

            BorrowRecord::create([
    'user_id' => $record->user_id,
    'book_id' => $record->book_id,
    'type' => 'return',
    'event_date' => now(),
    'notes' => 'Book returned'
]);

             return response()->json($record->load('user','book'));

        });



    }else{
        // return by user id and book id
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'book_id' => ['required', 'exists:books,id'],
        ]);

        return DB::transaction(function () use ($request) {
            $borrowRecord = BorrowRecord::where('user_id', $request->user_id)
    ->where('book_id', $request->book_id)
    ->where('type', 'borrow')
    ->latest()
    ->first();

            if (!$borrowRecord) {
                return response()->json(['message' => 'No active borrow record found for this user and book'], 400);
            }

            $book = Book::lockForUpdate()->findOrFail($borrowRecord->book_id);
            $book->stock = $book->stock + 1;
            $book->save();

            BorrowRecord::create([
    'user_id' => $request->user_id,
    'book_id' => $request->book_id,
    'type' => 'return',
    'event_date' => now(),
    'notes' => 'Book returned'
]);

            return response()->json($borrowRecord->load('user','book'));
        });
    }

}

// to get current borrow records
public function currentBorrows()
{
    $latestBorrows = BorrowRecord::where('type', 'borrow')
        ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('borrow_records as br2')
                ->whereRaw('br2.user_id = borrow_records.user_id')
                ->whereRaw('br2.book_id = borrow_records.book_id')
                ->where('br2.type', 'return')
                ->whereRaw('br2.event_date >= borrow_records.event_date');
        })
        ->with('user', 'book')
        ->orderByDesc('event_date')
        ->get();

    return response()->json($latestBorrows);
}

// to get borrow history

public function borrowHistory()
{
    $records = BorrowRecord::with('user', 'book')

        ->orderByDesc('event_date')
        ->get();
    return response()->json($records);
}
}




