<?php

namespace App\Http\Requests;

use App\Enums\Frequency;
use App\Enums\Period;
use App\Enums\Platform;
use App\Enums\Rawg\RawgGenre;
use App\Rules\Unique;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
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
            'name'                 => 'sometimes|string|min:5|max:255',
            'email'                => ['sometimes', 'email', new Unique('users', 'email', $this->user()->id)],
            'password'             => 'sometimes|string|min:6|max:18',
            'settings'             => 'sometimes|array',
            'settings.platforms'   => ['sometimes', 'array'],
            'settings.platforms.*' => ['required', 'string', 'in:' . Platform::valuesAsString()],
            'settings.genres'      => ['sometimes', 'array'],
            'settings.genres.*'    => ['required', 'string', 'in:' . RawgGenre::valuesAsString()],
            'settings.period'      => ['sometimes', 'string', 'in:' . Period::valuesAsString()],
            'settings.frequency'   => ['sometimes', 'string', 'in:' . Frequency::valuesAsString()],
        ];
    }
}
