<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\PostResource;
use App\Models\Customers;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search    = $request->search;
        $sort      = $request->sort ?? 'customer_name';
        $sort_type = $request->sort_type ?? 'asc';

        $query = Customers::query();

        if(!empty($search)){
            $query->where(function ($q) use ($search){
                $q->where('customer_name','LIKE','%'.$search.'%');
                $q->orWhere('customer_number','LIKE','%'.$search.'%');
            });
        }

        $customers = $query->orderBy($sort, $sort_type)->paginate(10);
        
        return new PostResource(true, 'Successfully get customers data', $customers);
    }

    public function store(CustomerRequest $request)
    {
        $post = $request->all();

        try {
            $customer = Customers::create([
                'customer_number'       => $post['customer_number'],
                'customer_name'         => $post['customer_name'],
                'customer_birthdate'    => $post['customer_birthdate'],
                'customer_status'       => 1
            ]);
    
            return new PostResource(true, 'Successfully create customer data', $customer);
        } catch (\Throwable $th) {
            return new PostResource(false, 'Failed to create customer data. Please try again later.', $th->getMessage());
        }
    }

    public function update(CustomerRequest $request, $id)
    {
        $post = $request->all();

        $customer = Customers::find($id);

        if(!$customer){
            return new PostResource(false, 'Customer not found', 404);
        }

        $customer->customer_number      = $post['customer_number'];
        $customer->customer_name        = $post['customer_name'];
        $customer->customer_birthdate   = $post['customer_birthdate'];
        $customer->customer_status      = $post['customer_status'] ?? $customer->customer_status;
        
        try {
            $customer->save();
            return new PostResource(true, 'Successfully update customer data', $customer);
        } catch (\Throwable $th) {
            return new PostResource(false, 'Failed to update customer data. Please try again later.', $th->getMessage());
        }
    }

    public function show($id)
    {
        $customer = Customers::find($id);

        if(!$customer){
            return new PostResource(false, 'Customer not found', 404);
        }

        return new PostResource(true, 'Successfully get customer detail', $customer);
    }

    public function destroy($id)
    {
        $customer = Customers::find($id);

        if(!$customer){
            return new PostResource(false, 'Customer not found', 404);
        }

        try {
            $customer->delete();
            return new PostResource(true, 'Successfully delete customer data', 200);
        } catch (\Throwable $th) {
            return new PostResource(false, 'Failed to delete customer data. Please try again later.', $th->getMessage());
        }
    }
}
