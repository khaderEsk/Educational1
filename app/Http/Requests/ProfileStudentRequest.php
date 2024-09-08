<?php

namespace App\Http\Requests;

use App\Traits\GeneralTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ProfileStudentRequest extends FormRequest
{
    use GeneralTrait;

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'educational_level'=>'sometimes|string',
            'phone' =>
                'sometimes|regex:/^09\d{8}$/
                |unique:profile_students',
            'name'=>'sometimes|string',
            'image' => 'sometimes|image|mimes:jpeg,jpg,png,gif',

        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->returnValidationError('422', $validator));
    }
}
