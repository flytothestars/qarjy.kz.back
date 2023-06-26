<?php

namespace App\Http\Requests\Statistics;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class TotalRequest extends ApiRequest
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
            'from' => "sometimes|date",
            'to' => "sometimes|date",
        ];
    }
}
