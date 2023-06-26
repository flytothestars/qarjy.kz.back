<?php

namespace App\Http\Requests\ExpenseCategory;

use App\Http\Requests\ApiRequest;

class ExprenseCategoryStore extends ApiRequest
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
            'title' => 'required|unique:expense_categories,title'
        ];
    }
}
