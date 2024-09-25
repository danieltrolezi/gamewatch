<?php

namespace App\Http\Requests;

use App\Enums\Discord\ComponentType;
use App\Enums\Discord\InteractionType;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class DiscordInteractionRequest extends FormRequest
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
        $rules = [
            'type'             => 'required|integer|in:' . InteractionType::valuesAsString(),
            'user'             => 'required|array',
            'user.id'          => 'required|string',
            'user.username'    => 'required|string',
            'user.global_name' => 'required|string',
        ];

        $additionalRules = match ($this->get('type')) {
            InteractionType::Command->value => $this->getCommandRules(),
            InteractionType::MessageComponent->value => $this->getMessageComponentRules(),
            default => []
        };

        return array_merge($rules, $additionalRules);
    }

    /**
     * @return array
     */
    private function getCommandRules(): array
    {
        return [
            'channel.id'           => 'required|string',
            'data'                 => 'required|array',
            'data.type'            => 'required|int',
            'data.name'            => 'required|string',
            'data.options'         => 'sometimes|array',
            'data.options.*'       => 'required|array',
            'data.options.*.value' => 'required'
        ];
    }

    /**
     * @return array
     */
    private function getMessageComponentRules(): array
    {
        return [
            'channel.id'          => 'required|string',
            'data'                => 'required|array',
            'data.component_type' => 'required|int|in:' . ComponentType::valuesAsString(),
            'data.custom_id'      => 'required|string',
            'data.values'         => 'sometimes|array',
            'message.components'  => 'sometimes|array'
        ];
    }

    /**
     * @param [type] $key
     * @param [type] $default
     * @return array
     */
    #[Override]
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);
        $validated['type'] = InteractionType::from($validated['type']);

        return $validated;
    }
}
