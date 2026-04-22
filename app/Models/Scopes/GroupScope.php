<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class GroupScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->hasUser() && auth()->user()->role !== 'dins') {
            $user = auth()->user();
            
            if ($user->current_group_id) {
                $builder->where($model->getTable() . '.group_id', $user->current_group_id);
            } else {
                // Personal mode: Filter by user_id and ensure group_id is NULL
                $builder->where($model->getTable() . '.user_id', $user->id)
                        ->whereNull($model->getTable() . '.group_id');
            }
        }
    }
}
