<?php

namespace Qintuap\Scopes;

use Exception;
use Qintuap\Repositories\Contracts\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Scope as EloquentScope;
use Qintuap\Repositories\Repos;
use Qintuap\CacheDecorators\Contracts\CacheableScopes;
use Qintuap\CacheDecorators\Facades\DecoCache;

/**
 * Scope class that uses an other callable method as the scope.
 *
 * @author Premiums
 */
class ScopeCall extends Scope {
    
    var $callable;
    var $parameters;
    
    public function __construct($callable, $arguments = [], $cache_tags = [])
    {
        $this->setCallable($callable);
        $this->parameters = $arguments;
        $this->tags = $cache_tags;
    }
    
    protected function setCallable($callable) {
        
        if(is_array($callable) 
                && !($callable[0] instanceof CacheableScopes) 
                && DecoCache::canDecorate($callable[0])) {
            $callable[0] = DecoCache::decorate($callable[0]);
        }
        $this->callable = $callable;
    }


    public function apply(Builder $query, Model $model) {
        $parameters = array_merge([], [$query], $this->parameters);
        return call_user_func_array($this->callable,$parameters);
    }
    
    public function useCache() {
        $callable = $this->callable;
        
        if(is_array($callable)) {
            return $callable[0] instanceof CacheableScopes && $callable[0]
                    ->useScopeCache($callable[1], $this->parameters);
        } else {
            return $this->cache_key;
        }
    }
    
    public function getCacheKey() {
        $callable = $this->callable;
        if(is_array($callable) && ($callable[0] instanceof CacheableScopes) ) {
            $cache_key = $callable[0]->makeScopeCacheKey($callable[1], $this->parameters);
        } elseif(is_string($callable)) {
            throw new Exception('string callable not yet supported');
//            $this->cache_key = md5(json_encode(array(
//                    $this->callable,
//                    $this->parameters
//                )));
        } elseif($this->cache_key) {
            $cache_key = $this->cache_key;
        } else {
            \Debugbar::addMessage($callable, 'warning');
            throw new Exception('no valid callable given');
        }
        return $cache_key;
    }
    
    public function getCacheTags() {
        
        $callable = $this->callable;
        $tags = $this->cache_tags;
//        if(is_array($callable) && ($callable[0] instanceof Model || $callable[0] instanceof Repository)) {
        if(is_array($callable) && ($callable[0] instanceof CacheableScopes) ) {
            $tags = array_merge($tags, $callable[0]->makeScopeCacheTags($callable[1], $this->parameters));
        } elseif(is_string($callable)) {
            throw new Exception('string callable not yet supported');
//            $this->cache_key = md5(json_encode(array(
//                    $this->callable,
//                    $this->parameters
//                )));
        } else {
            \Debugbar::addMessage($callable, 'warning');
            throw new Exception('no valid callable given');
        }
        return $tags;
    }
    
    public function getName() {
        if(is_array($this->callable)) {
            return class_basename($this->callable[0]) .'::'.$this->callable[1];
        } elseif(is_string($this->callable)) {
            return $this->callable;
        } else {
            return '{anonymous}';
        }
    }
}
