<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseRepository
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function find(string $id): ?Model
    {
        return $this->model->where('uuid', $id)->first();
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}