<?php

namespace App\Http\Resources\Templates;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithDataResource extends JsonResource
{
    public $status;
    public $title;
    public $description;
    public $data;

    public function __construct($status, $title, $description, $data = null)
    {
        parent::__construct($data);
        $this->status = $status;
        $this->title = $title;
        $this->description = $description;
        $this->data = $data;
    }

    public function toArray(Request $request): array
    {
        return [
            'status' => $this->status,
            'message' => [
                'title' => $this->title,
                'description' => $this->description,
                'data' => $this->data
            ],
        ];
    }
}
