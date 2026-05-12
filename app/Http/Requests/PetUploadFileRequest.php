<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of PetUploadFileRequest
 */
class PetUploadFileRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'file'               => ['required', 'file', 'mimes:jpeg,png,gif', 'max:10240'],
            'additionalMetadata' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Plik jest wymagany.',
            'file.file'     => 'Przesłany element musi być plikiem.',
            'file.mimes'    => 'Plik musi być w formacie: jpeg, png, gif.',
            'file.max'      => 'Plik nie może przekraczać 10MB.',
        ];
    }

    /**
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('additionalMetadata')) {
            $this->merge([
                'additionalMetadata' => trim($this->input('additionalMetadata')),
            ]);
        }
    }
}
