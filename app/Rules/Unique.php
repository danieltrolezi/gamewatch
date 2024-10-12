<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use RuntimeException;

class Unique implements ValidationRule
{
    /**
     * @param string $collection
     * @param string $field
     * @param string|null $ignoreId
     */
    public function __construct(
        private string $collection,
        private string $field,
        private ?string $ignoreId = null
    ) {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $model = 'App\\Models\\' . Str::singular(Str::studly($this->collection));

        if (!class_exists($model)) {
            throw new RuntimeException("Model class {$model} does not exist.");
        }

        $query = $model::where($this->field, $value);

        if (!empty($this->ignoreId)) {
            $query = $query->where('id', $this->ignoreId, '!=');
        }

        if (!empty($query->first())) {
            $fail("The :attribute {$value} is already in use.");
        }
    }
}
