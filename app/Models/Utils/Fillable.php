<?php

namespace App\Models\Utils;

use Illuminate\Support\Str;

trait Fillable
{
    protected function fill(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->{Str::camel($key)} = $value;
        }
    }
}
