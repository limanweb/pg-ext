<?php

namespace Limanweb\PgExt\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Limanweb\PgExt\Support\PgHelper;

class HasManyInArray extends ArrayRelation
{

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {

            $parentValue = $this->parent->{$this->arrayField};
            if (is_string($parentValue)) {
                $parentValue = PgHelper::fromPgArray($parentValue);
            }

            if (!is_array($parentValue)) {
                throw new \RuntimeException("Can't cast field value");
            }

            $this->query->whereIn($this->query->qualifyColumn($this->relatedKey), $this->parent->{$this->arrayField});

        }
    }

    /**
     * Get all of the foreign keys for an array of models.
     *
     * @param  array   $models
     * @param  string  $key
     * @return array
     */
    protected function getRelatedKeys(array $models, $key = null)
    {
        $keys = [];
        collect($models)->map(function ($value) use ($key, &$keys) {
            if (is_array($arrayFieldValue = $value->getAttribute($key))) {
                $keys = array_merge($keys, $arrayFieldValue);
            }
        });

        return array_unique($keys);
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->query->whereIn(
            $this->query->qualifyColumn($this->relatedKey), $this->getRelatedKeys($models, $this->arrayField)
        );

    }

    /**
     * [Description] TODO
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result->{$this->relatedKey}] = $result;
        }

        return $dictionary;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $keys = $model->getAttribute($this->arrayField);
            $relatedItems = null;
            if (!empty($keys)) {
                $relatedItems = array_values(array_intersect_key($dictionary, array_flip($keys)));

            }
            $model->setRelation(
                $relation,
                empty($relatedItems) ? null : $this->related->newCollection($relatedItems)
                );
        }

        return $models;
    }

    /**
     * [Description] TODO
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        // Check for parent model arrayField has an child model relatedKey value
        return $query->select($columns)->whereRaw(
            "{$this->parent->qualifyColumn($this->arrayField)}{$this->getCastAs(true)} @> ARRAY[{$this->query->qualifyColumn($this->relatedKey)}{$this->getCastAs()}]"
        );

    }

    /**
     * Attach a model to the parent.
     *
     * @param  mixed  $id
     * @param  array  $attributes
     * @param  bool   $touch
     * @return void
     */
    public function attach($ids, $touch = true)
    {
        if (!is_null($ids)) {
            // Add attached id into parent arrayField
            $val = $this->parent->{$this->arrayField} ?? [];
            if (!is_array($ids)) {
                $ids = [$ids];
            }
            $val = array_merge($val, $ids);
            $this->parent->setAttribute($this->arrayField, array_values($val))->save();
        }

        return null;

    }

    /**
     * Detach models from the relationship.
     *
     * @param  mixed  $ids
     * @param  bool  $touch
     * @return void
     */
    public function detach($ids = null, $touch = true)
    {
        if (!is_null($ids)) {
            // Remove attached id from parent arrayField
            $val = $this->parent->{$this->arrayField} ?? [];
            if (is_array($ids)) {
                $val = array_diff($val, $ids);
            } else {
                $val = array_diff($val, [$ids]);
            }
            $this->parent->setAttribute($this->arrayField, array_values($val))->save();
        }

        return null;

    }

    /**
     * Toggles a model (or models) from the parent.
     *
     * Each existing model is detached, and non existing ones are attached.
     *
     * @param  mixed  $ids
     * @param  bool   $touch
     * @return array
     */
//     public function toggle($ids, $touch = true)
//     {

//         $changes = [
//             'attached' => [], 'detached' => [],
//         ];

//         $val = $this->parent->{$this->arrayField} ?? [];
//         if (!is_array($ids)) {
//             $ids = [$ids];
//         }

//         $changes['attached'] = array_diff($ids, $val);
//         $changes['detached'] = array_intersect($ids, $val);

//         $val = array_merge($val, $changes['attached']);
//         $val = array_diff($val, $changes['detached']);

//         $this->parent->setAttribute($this->arrayField, array_values($val))->save();

//         return null;

//     }


}

?>