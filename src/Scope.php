<?php

namespace Qintuap\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Scope as EloquentScope;

/**
 * Description of Scope
 *
 * @author Premiums
 */
abstract class Scope implements EloquentScope {
    
    /**
     * The unique cache key used to cache the results
     * Leave false to not cache.
     */
    var $cache_key = false;
    var $cache_tags = [];

    public abstract function apply(Builder $query, Model $model);
    
    public function useCache() {
        return !$this->cache_key === false;
    }
    
    public function getCacheKey() {
        return $this->cache_key;
    }
    
    public function getCacheTags() {
        return $this->cache_tags;
    }
    
    public function getName() {
        return class_basename($this);
    }

}
