<?php

namespace App\Http\Resources\Templates;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithoutDataResource extends JsonResource
{
    public $status;
    public $title;
    public $description;

    public function __construct($status, $title, $description)
    {
        parent::__construct(null);
        $this->status = $status;
        $this->title = $title;
        $this->description = $description;
    }

    public function toArray(Request $request): array
    {
        return [
            'status' => $this->status,
            'message' => [
                'title' => $this->title,
                'description' => $this->description
            ],
        ];
    }
}
