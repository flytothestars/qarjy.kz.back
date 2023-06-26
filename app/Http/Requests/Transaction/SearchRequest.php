<?php

namespace App\Http\Requests\Transaction;

use App\Http\Requests\ApiRequest;

class SearchRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'type' => "sometimes|in:expense,income",
            'from' => "sometimes|date",
            'to' => "sometimes|date",
            'expense_root_category_id' => 'sometimes|exists:expense_categories,id',
            'expense_secondary_category_id' => 'sometimes|exists:expense_categories,id',
            # 'expense_final_category_id' => 'sometimes|array|exists:expense_categories,id',
            #  'expense_final_category_id.*' => 'exists:expense_categories,id',
        ];
    }
}
