<?php

namespace Limanweb\PgExt\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

abstract class ArrayRelation extends Relation
{

    /**
     * [Description] TODO
     *
     * @var string
     */
    protected $arrayField;

    /**
     * [Description] TODO
     *
     * @var string
     */
    protected $relatedKey;

    protected $castAs;

    /**
     * Constructor
     *
     * @param Builder $query
     * @param Model $related
     * @param null|string $arrayField
     * @param null|string $relatedKey
     * @param null|string $castAs
     */
    public function __construct(Builder $query, Model $parent, $arrayField, $relatedKey, $castAs = null)
    {
        $this->arrayField = $arrayField;
        $this->relatedKey = $relatedKey;
        $this->castAs = $castAs;

        parent::__construct($query, $parent);
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array   $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {

        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->get();
    }

    protected function getCastAs($isArray = false)
    {
        if (is_null($this->castAs)) {
            return "";
        }

        return "::{$this->castAs}" . ($isArray ? "[]" : "");
    }

}

?>