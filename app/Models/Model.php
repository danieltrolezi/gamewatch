<?php

namespace App\Models;

use App\Models\Utils\Fillable;
use Illuminate\Support\Str;
use JsonSerializable;

abstract class Model implements JsonSerializable
{
    use Fillable;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public function jsonSerialize(): mixed
    {
        $data = [];
        $attributes = get_object_vars($this);

        foreach ($attributes as $key => $value) {
            $data[Str::snake($key)] = $value;
        }

        return $data;
    }
}
