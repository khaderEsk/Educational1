<?php

namespace App\Http\Requests;

use App\Traits\GeneralTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FavouriteRequest extends FormRequest
{
    use GeneralTrait;


    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'profile_teacher_id'=>'integer|required'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->returnValidationError('422', $validator));

    }
}
