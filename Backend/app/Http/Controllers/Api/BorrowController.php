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
                ->whereNull('returned_at')
                ->first();

            if ($existingBorrow) {

                return response()->json(['message' => 'User has already borrowed this book and not returned yet'], 400);
            }

            // to decrease stock

            $book->stock = $book->stock - 1;
            $book->save();


            // to create borrow record

            BorrowRecord::create([
                'user_id' => $request->user_id,
                'book_id' => $request->book_id,
                'borrowed_at' => now(),
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

            if ($record->returned_at) {
                return response()->json(['message' => 'Book has already returned'], 400);

            }

            $book = Book::lockForUpdate()->findOrFail($record->book_id);
            $book->stock = $book->stock + 1;
            $book->save();

            $record->returned_at = now();
            $record->save();

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
                ->whereNull('returned_at')
                ->lockForUpdate()
                ->firstOrFail();

            if (!$borrowRecord) {
                return response()->json(['message' => 'No active borrow record found for this user and book'], 400);
            }

            $book = Book::lockForUpdate()->findOrFail($borrowRecord->book_id);
            $book->stock = $book->stock + 1;
            $book->save();

            $borrowRecord->returned_at = now();
            $borrowRecord->save();

            return response()->json($borrowRecord->load('user','book'));
        });
    }

}

// to get current borrow records
public function currentBorrows()
{
    $records = BorrowRecord::with('user', 'book')
        ->whereNull('returned_at')
        ->orderByDesc('borrowed_at')
        ->get();
    return response()->json($records);
}

// to get borrow history

public function borrowHistory()
{
    $records = BorrowRecord::with('user', 'book')
        ->whereNotNull('returned_at')
        ->orderByDesc('returned_at')
        ->get();
    return response()->json($records);
}
}




