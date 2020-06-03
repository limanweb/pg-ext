<?php

namespace Limanweb\PgExt\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Limanweb\PgExt\Support\PgHelper;

class BelongsToManyArrays extends ArrayRelation
{

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {

            $relatedKeys = PgHelper::toPgArray([$this->parent->{$this->relatedKey}]);
            $this->query->whereRaw("{$this->query->qualifyColumn($this->arrayField)} @> '{$relatedKeys}'");

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
            $keys[] = $value->getAttribute($key);
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
        $arrayFieldValues = PgHelper::toPgArray($this->getRelatedKeys($models, $this->parent->getKeyName()));

        $this->query->whereRaw(
            "{$this->query->qualifyColumn($this->arrayField)} && '{$arrayFieldValues}'"
        );
    }

    /**
     * [Description] TODO
     *
     * @param Collection $results
     * @return array|unknown
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        collect($results)->map(function ($value) use (&$dictionary) {

            foreach ($value->{$this->arrayField} as $arrayValue) {
                $dictionary[$arrayValue][] = &$value;
            }

        });

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
            $key = $model->getAttribute($this->relatedKey);

            $relatedItems = $dictionary[$model->getAttribute($this->relatedKey)] ?? [];
            $model->setRelation(
                $relation,
                empty($relatedItems) ? null : $this->related->newCollection($relatedItems)
            );
        }

        return $models;
    }

    /**
     *
     * {@inheritDoc}
     * @see \Illuminate\Database\Eloquent\Relations\Relation::getRelationExistenceQuery()
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        // Check for parent model relatedKey exists in child model arrayField
        return $query->select($columns)->whereRaw(
            "{$this->query->qualifyColumn($this->arrayField)}{$this->getCastAs(true)} @> ARRAY[{$this->parent->qualifyColumn($this->relatedKey)}{$this->getCastAs()}]"
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

            $ids = (is_array($ids)) ? $ids : [$ids];

            $models = $this->related->getModel()->whereIn($this->query->qualifyColumn($this->query->getModel()->getKeyName()), $ids)->get();
            $parentId = $this->parent->{$this->relatedKey};
            foreach ($models as $model) {
                $val = $model->{$this->arrayField};
                $val = array_unique(array_merge($val, [$parentId]));
                $model->setAttribute($this->arrayField, array_values($val))->save();
            }
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

            $ids = (is_array($ids)) ? $ids : [$ids];

            $models = $this->whereIn($this->query->qualifyColumn($this->query->getModel()->getKeyName()), $ids)->get();
            $parentId = $this->parent->{$this->relatedKey};
            foreach ($models as $model) {
                $val = $model->{$this->arrayField};
                $val = array_unique(array_diff($val, [$parentId]));
                $model->setAttribute($this->arrayField, array_values($val))->save();
            }
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
