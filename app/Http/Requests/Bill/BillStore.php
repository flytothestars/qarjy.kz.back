<?php

namespace App\Http\Requests\Bill;

use App\Http\Requests\ApiRequest;
use App\Models\Bill;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class BillStore extends ApiRequest
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
        $user = $this->user();
        return [
            "url" => ["required", "url", function ($attribute, $value, $fail) use ($user) {
                if (Bill::where("user_id", $user->id)->where("url", $value)->exists()) {
                    $fail("Чек уже загружен");
                }
            },]
        ];
    }
}
