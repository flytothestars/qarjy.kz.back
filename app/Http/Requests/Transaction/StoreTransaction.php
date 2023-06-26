<?php

namespace App\Http\Requests\Transaction;

use App\Http\Requests\ApiRequest;

class StoreTransaction extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'type' => "required|in:income,expense",
            'income_category_id' => 'required_if:type,income|exists:income_categories,id',
            'expense_root_category_id' => 'required_if:type,expense|exists:expense_categories,id',
            'title' => 'required_if:type,expense',
            'price' => 'required|min:0|numeric',
            'quantity' => 'required_if:type,expense|min:0|numeric',
            'company' => 'required|string|max:255'
        ];
    }
}
