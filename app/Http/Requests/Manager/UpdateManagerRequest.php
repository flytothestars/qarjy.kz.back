<?php

namespace App\Http\Requests\Manager;

use App\Http\Requests\ApiRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateManagerRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            "name" => "required|max:255",
            "email" => ['required','email',Rule::unique('users','email')->ignore($this->route('managerId'))],
            "phone" => ["required", Rule::unique('users','phone')->ignore($this->route('managerId'))],
        ];
    }

    protected function prepareForValidation()
    {
        if ($phone = request()->input("phone")) {
            $this->request->set("phone", User::phoneToDigits($phone));
        }
    }
}
