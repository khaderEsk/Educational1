<?php

namespace App\Http\Requests;

use App\Traits\GeneralTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProfileTeacherRequest extends FormRequest
{
    use GeneralTrait;

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'certificate' => 'sometimes|image|mimes:jpeg,jpg,png,gif',
            'description' => 'sometimes',
            'jurisdiction' => 'sometimes|string',
            'image' => 'sometimes|image|mimes:jpeg,jpg,png,gif',
            'address'=>'sometimes|string',
            'governorate'=>'sometimes|string',
            'domains'=>'sometimes|array',
            'domains.*.id'=>'sometimes|integer',
            'domains.*.type'=>'sometimes|string',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->returnValidationError('422', $validator));
    }
}
