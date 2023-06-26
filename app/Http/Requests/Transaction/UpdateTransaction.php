<?php

namespace App\Http\Requests\Transaction;

use App\Http\Requests\ApiRequest;
use App\Models\Transaction;

class UpdateTransaction extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $transaction = Transaction::findOrFail($this->route("transaction"));
        return auth()->check() && $this->user()->id == $transaction->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'price' => 'required|min:0|numeric',
            'quantity' => 'required_if:type,expense|min:1|numeric',
            'company' => 'required|string|max:255'
        ];
    }
}
