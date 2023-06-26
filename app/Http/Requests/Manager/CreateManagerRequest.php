<?php

namespace App\Http\Requests\Manager;

use App\Http\Requests\ApiRequest;
use App\Models\User;

class CreateManagerRequest extends ApiRequest
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
            "name" => "required|max:255",
            "email" => "required|email|unique:users,email",
            "phone" => ["required", "unique:users,phone"],
        ];
    }

    protected function prepareForValidation()
    {
        if ($phone = request()->input("phone")) {
            $this->request->set("phone", User::phoneToDigits($phone));
        }
    }
}
