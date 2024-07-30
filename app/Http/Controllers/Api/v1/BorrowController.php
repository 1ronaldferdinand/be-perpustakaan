<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowRequest;
use App\Http\Resources\PostResource;
use App\Models\Books;
use App\Models\Borrows;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\PostDec;

class BorrowController extends Controller
{
    protected $bookController;

    public function __construct(BookController $bookController)
    {
        $this->bookController = $bookController;
    }

    public function index(Request $request)
    {
        $search    = $request->search;
        $sort_name = $request->sort ?? 'borrow_start';
        $sort_type = $request->sort_type ?? 'desc';
        $page      = $request->page ?? 1;
        $perPage   = $request->per_page ?? 10;
    
        $query = Borrows::with(['customer', 'book']);
    
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function($query) use ($search) {
                    $query->where('customer_name', 'LIKE', '%' . $search . '%');
                })
                ->orWhereHas('book', function($query) use ($search) {
                    $query->where('book_title', 'LIKE', '%' . $search . '%');
                });
            });
        }
    
        $sortableColumns = [
            'customer_name' => 'customers.customer_name',
            'book_title'    => 'books.book_title',
            'book_price'    => 'books.book_price',
            'borrow_start'  => 'borrows.borrow_start', 
            'borrow_end'    => 'borrows.borrow_end',
        ];

        $typeOfSorting = ['asc', 'ASC', 'desc', 'DESC'];
    
        if (array_key_exists($sort_name, $sortableColumns) && in_array($sort_type, $typeOfSorting)) {
            $query->join('customers', 'borrows.customer_id', '=', 'customers.id')
                  ->join('books', 'borrows.book_id', '=', 'books.id')
                  ->orderBy($sortableColumns[$sort_name], $sort_type);
        } else {
            $query->orderBy('borrows.borrow_start', 'desc');
        }
    
        $query   = $query->select('borrows.*');
        $borrows = $query->paginate($perPage, ['*'], 'page', $page);
    
        return new PostResource(true, 'Successfully got borrows data', $borrows);
    }
    
    public function borrow(BorrowRequest $request)
    {
        $post = $request->all();
        
        $book = Books::where('id', '=', $post['book_id'])->where('book_status', 1)->first();

        if(!$book){
            return new PostResource(false, 'Book not found', 404);
        }

        if($book->book_stocks < 1){
            return new PostResource(false, 'Book stock is not enough', 400);
        }

        try {
            DB::beginTransaction();

            $borrow = Borrows::create([
                'book_id'       => $post['book_id'],
                'customer_id'   => $post['customer_id'],
                'borrow_start'  => $post['borrow_start'],
                'borrow_end'    => null,
                'borrow_status' => 1
            ]);

            $updateStockResponse = $this->bookController->updateStock(new Request(['book_borrow' => true]), $post['book_id']);
            $updateStockResponseData = json_decode($updateStockResponse->getContent(), true);

            if ($updateStockResponse->status() === 200 && $updateStockResponseData['success']) {
                DB::commit();
                return new PostResource(true, 'Successfully borrowed book', $borrow);
            } else {
                DB::rollBack();
                return new PostResource(false, $updateStockResponseData['message'], 400);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return new PostResource(false, 'Failed to borrow book. Please try again later.', $th->getMessage());
        }
    }

    public function unborrow($id)
    {
        $borrow = Borrows::find($id);

        if (!$borrow) {
            return new PostResource(false, 'Borrow record not found', 404);
        }

        if ($borrow->borrow_status != 1) {
            return new PostResource(false, 'Borrow record is not currently borrowed', 400);
        }

        try {
            DB::beginTransaction();

            $borrow->borrow_status = 2;
            $borrow->borrow_end = now();
            $borrow->updated_at = now();
            $borrow->save();

            $bookController = new BookController();
            $bookController->updateStock(new Request(['book_borrow' => false]), $borrow->book_id);

            DB::commit();
            return new PostResource(true, 'Successfully updated borrow status to returned', $borrow);
        } catch (\Throwable $th) {
            DB::rollBack();
            return new PostResource(false, 'Failed to update borrow status. Please try again later.', $th->getMessage());
        }
    }

    public function show($id){
        $borrow = Borrows::where('id', $id)->with(['book', 'customer'])->first();

        if (!$borrow) {
            return new PostResource(false, 'Borrow record not found', 404);
        }

        return new PostResource(true, 'Successfully get borrow detail', $borrow);
    }

    public function destroy($id){
        $borrow = Borrows::find($id);

        if(!$borrow){
            return new PostResource(false, 'Borrow record not found', 404);
        }

        try {
            $borrow->delete();
            return new PostResource(true, 'Successfully delete borrow data', 200);
        } catch (\Throwable $th) {
            return new PostResource(false, 'Failed to delete borrow data. Please try again later.', $th->getMessage());
        }
    }
}
