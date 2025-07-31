<?php

namespace App\Http\Requests\Auth;

use App\Http\Resources\Templates\WithoutDataResource;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class LoginDashboardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string', 'min:4'],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Silahkan masukkan email atau username anda terlebih dahulu.',
            'password.required' => 'Kolom password wajib diisi terlebih dahulu.',
            'password.min' => 'Minimum password yang harus diisi adalah 4 karakter.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $messages = implode(' ', $validator->errors()->all());
        $response = new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Login Gagal', $messages);

        throw new HttpResponseException(response()->json($response, Response::HTTP_BAD_REQUEST));
    }
}
