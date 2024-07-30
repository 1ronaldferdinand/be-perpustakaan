<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BorrowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'book_id'      => 'required',
            'customer_id'  => 'required',
            'borrow_start' => 'required|date_format:Y-m-d',
        ];
    }

    public function messages(): array
    {
        return [
            'borrow_start.date_format'  => 'The borrow start must be in the format yyyy-mm-dd.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors();
        $response = response()->json([
            'success' => false,
            'message' => 'Input Error',
            'data' => $errors
        ], 422);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}
