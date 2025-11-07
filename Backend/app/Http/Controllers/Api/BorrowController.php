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

    // get all borrow records
    public function index()
    {
        $records = BorrowRecord::with('user', 'book')->orderByDesc('event_date')->get();
        return response()->json($records);
    }
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
            $latestRecord = BorrowRecord::where('user_id', $request->user_id)
                ->where('book_id', $request->book_id)
                ->latest()
                ->first();

            if ($latestRecord && $latestRecord->type === 'borrow') {
                return response()->json(['message' => 'User has already borrowed this book and not returned yet'], 400);
            }

            // to decrease stock

            $book->stock = $book->stock - 1;
            $book->save();


            // to create borrow record

            BorrowRecord::create([
                'user_id' => $request->user_id,
                'book_id' => $request->book_id,
                'type' => 'borrow',
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

            // Check if this borrow record has already been returned
            $latestRecord = BorrowRecord::where('user_id', $record->user_id)
                ->where('book_id', $record->book_id)
                ->latest()
                ->first();

            if ($latestRecord && $latestRecord->type === 'return') {
                return response()->json(['message' => 'Book has already been returned'], 400);
            }




            $book = Book::lockForUpdate()->findOrFail($record->book_id);
            $book->stock = $book->stock + 1;
            $book->save();

            $returnRecord = BorrowRecord::create([
    'user_id' => $record->user_id,
    'book_id' => $record->book_id,
    'type' => 'return',
    'event_date' => now(),
    'notes' => 'Book returned'
]);

             return response()->json($returnRecord->load('user','book'));

        });



    }else{
        // return by user id and book id
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'book_id' => ['required', 'exists:books,id'],
        ]);

        return DB::transaction(function () use ($request) {
            $latestRecord = BorrowRecord::where('user_id', $request->user_id)
                ->where('book_id', $request->book_id)
                ->latest()
                ->first();

            if (!$latestRecord || $latestRecord->type !== 'borrow') {
                return response()->json(['message' => 'No active borrow record found for this user and book'], 400);
            }

            $book = Book::lockForUpdate()->findOrFail($latestRecord->book_id);
            $book->stock = $book->stock + 1;
            $book->save();

            $returnRecord = BorrowRecord::create([
    'user_id' => $request->user_id,
    'book_id' => $request->book_id,
    'type' => 'return',
    'event_date' => now(),
    'notes' => 'Book returned'
]);

            return response()->json($returnRecord->load('user','book'));
        });
    }

}

// to get current borrow records
public function currentBorrows()
{
    $borrowRecords = BorrowRecord::where('type', 'borrow')
        ->with('user', 'book')
        ->get();

    $activeBorrows = collect();

    foreach ($borrowRecords as $record) {
        $latestRecord = BorrowRecord::where('user_id', $record->user_id)
            ->where('book_id', $record->book_id)
            ->latest()
            ->first();

        if ($latestRecord && $latestRecord->type === 'borrow') {
            $activeBorrows->push($record);
        }
    }

    return response()->json($activeBorrows->sortByDesc('event_date')->values());
}

// to get borrow history

public function borrowHistory()
{
    $records = BorrowRecord::with('user', 'book')

        ->orderByDesc('event_date')
        ->get();
    return response()->json($records);
}

// to get current borrows for a specific user
public function userCurrentBorrows($userId)
{
    // Get the latest record for each book the user has interacted with
    $latestRecords = BorrowRecord::where('user_id', $userId)
        ->selectRaw('MAX(id) as id')
        ->groupBy('user_id', 'book_id')
        ->get()
        ->pluck('id');

    $borrowRecords = BorrowRecord::whereIn('id', $latestRecords)
        ->where('type', 'borrow')
        ->with('user', 'book')
        ->orderByDesc('event_date')
        ->get();

    return response()->json($borrowRecords);
}
}




