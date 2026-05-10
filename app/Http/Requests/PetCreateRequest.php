<?php

namespace App\Http\Requests;

/**
 * Summary of PetCreateRequest
 */
class PetCreateRequest extends Request
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'status'        => ['required', 'string', 'in:available,pending,sold'],
            'photoUrls'     => ['nullable', 'array'],
            'photoUrls.*'   => ['nullable', 'url'],
            'category.id'   => ['nullable', 'integer'],
            'category.name' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required'   => 'Nazwa zwierzęcia jest wymagana.',
            'name.max'        => 'Nazwa nie może przekraczać 255 znaków.',
            'status.required' => 'Status jest wymagany.',
            'status.in'       => 'Status musi być jednym z: available, pending, sold.',
            'photoUrls.*.url' => 'Każde zdjęcie musi być poprawnym adresem URL.',
        ];
    }

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
    }
}
