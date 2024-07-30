<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookRequest;
use App\Http\Resources\PostResource;
use App\Models\Books;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $search    = $request->search;
        $sort      = $request->sort ?? 'book_title';
        $sort_type = $request->sort_type ?? 'asc';

        $query     = Books::query();

        if(!empty($query)){
            $query->where(function ($q) use ($search){
                $q->where('book_title', 'LIKE', '%'.$search.'%');
                $q->orWhere('book_publisher', 'LIKE', '%'.$search.'%');
            });
        }

        $books     = $query->orderBy($sort, $sort_type)->paginate(10); 
        
        return new PostResource(true, 'Successfully get books data', $books);
    }

    public function store(BookRequest $request)
    {
        $post = $request->all();

        try {
            $book = Books::create([
                'book_title'     => $post['book_title'],
                'book_publisher' => $post['book_publisher'],
                'book_size'      => $post['book_size'] ?? 0,
                'book_price'     => $post['book_price'] ?? 0,
                'book_stocks'    => $post['book_stocks'] ?? 0,
                'book_status'    => 1,
            ]);
    
            return new PostResource(true, 'Successfully create book data', $book);
        } catch (\Throwable $th) {
            return new PostResource(false, 'Failed to create book data. Please try again later.', $th->getMessage());
        }
    }

    public function update(BookRequest $request, $id)
    {
        $post = $request->all();

        $book = Books::find($id);

        if(!$book){
            return new PostResource(false, 'Book not found', 404);
        }

        $book->book_title = $post['book_title'];
        $book->book_publisher = $post['book_publisher'];
        $book->book_size = $post['book_size'];
        $book->book_price = $post['book_price'];
        $book->book_stocks = $post['book_stocks'];
        $book->book_status = $post['book_status']?? $book->book_status; // 0 => inactive, 1 => active
        $book->updated_at = now();

        try {
            $book->save();
            return new PostResource(true, 'Successfully update book data', $book);
        } catch (\Throwable $th) {
            return new PostResource(false, 'Failed to update book data. Please try again later.', $th->getMessage());
        }
    }

    public function show($id)
    {
        $book = Books::find($id);

        if(!$book){
            return new PostResource(false, 'Book not found', 404);
        }

        return new PostResource(true, 'Successfully get book detail', $book);
    }

    public function destroy($id)
    {
        $book = Books::find($id);

        if(!$book){
            return new PostResource(false, 'Book not found', 404);
        }

        try {
            $book->delete();
            return new PostResource(true, 'Successfully delete book data', 200);
        } catch (\Throwable $th) {
            return new PostResource(false, 'Failed to delete book data. Please try again later.', $th->getMessage());
        }
    }

    public function updateStock(Request $request, $id)
    {
        $post = $request->all();

        $book = Books::find($id);
        
        if (!$book) {
            return response()->json(new PostResource(false, 'Book not found', null), 404);
        }

        $bookBorrow = $post['book_borrow'] ?? false;
        $book->book_stocks = $bookBorrow ? $book->book_stocks - 1 : $book->book_stocks + 1;
        $book->updated_at = now();

        try {
            $book->save();
            return response()->json(new PostResource(true, 'Successfully updated book stock data', $book), 200);
        } catch (\Throwable $th) {
            return response()->json(new PostResource(false, 'Failed to update book data. Please try again later.', $th->getMessage()), 500);
        }
    }
}
