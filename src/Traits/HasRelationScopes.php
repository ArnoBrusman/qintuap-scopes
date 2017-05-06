<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Qintuap\Scopes\Traits;

use Illuminate\Database\Eloquent\Model as EloquentModel;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

/**
 *
 * @author Premiums
 */
trait HasRelationScopes {

    public function scopeOfRelation($query, $relationName, $relation, \Closure $subscope = null, $or = false)
    {
        if($relation instanceof EloquentModel) {
            $id = $relation->getKey();
        } else {
            $id = $relation;
        }
        
        $relationQuery = $this->$relationName();
        
        if(is_null($subscope) && $relationQuery instanceof BelongsTo) {
            $foreignKey = $relationQuery->getForeignKey();
            $where = $or ? 'orWhere' : 'where';
            return $query->$where($foreignKey, $id);
        } else {
            $key = $relationQuery->getRelated()->getQualifiedKeyName();
            $whereHas = $or ? 'orWhereHas' : 'whereHas';
            return $query->$whereHas($relationName,function($query) use($key, $id, $subscope) {
                $query->where($key, '=', $id);
                if(!is_null($subscope)) {
                    $subscope($query);
                }
            });
        }
    }
    
    public function scopeOrOfRelation($query, $relationName, $relation,\Closure $subscope = null)
    {
        return $this->scopeOfRelation($query, $relationName, $relation, $subscope, true);
    }
}
