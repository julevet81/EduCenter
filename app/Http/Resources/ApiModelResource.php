<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiModelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource->toArray();

        unset($data['password'], $data['remember_token']);

        return $data;
    }
}
