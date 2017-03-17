<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Qintuap\Scopes\Traits;

use Illuminate\Database\Eloquent\Model as EloquentModel;
/**
 *
 * @author Premiums
 */
trait HasRelationScopes {

    public function scopeOfRelation($query, $relationName, $relation)
    {
        if($relation instanceof EloquentModel) {
            $key = $relation->getQualifiedKeyName();
            $id = $relation->getKey();
        } else {
            $key = $this->getRelationKeyName($relationName);
            $id = $relation;
        }
        return $query->whereHas($relationName,function($query) use($key, $id) {
            return $query->where($key, '=', $id);
        });
    }
    public function scopeOrOfRelation($query, $relationName, $relation)
    {
        if($relation instanceof EloquentModel) {
            $key = $relation->getQualifiedKeyName();
            $id = $relation->getKey();
        } else {
            $key = $this->getRelationKeyName($relationName);
            $id = $relation;
        }
        return $query->orWhereHas($relationName,function($query) use($key, $id) {
            return $query->where($key, '=', $id);
        });
    }
}
