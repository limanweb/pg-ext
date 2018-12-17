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
    
    /**
     * Constructor
     *
     * @param Builder $query
     * @param Model $related
     * @param null|string $arrayField
     * @param null|string $relatedKey
     */
    public function __construct(Builder $query, Model $parent, $arrayField, $relatedKey)
    {
        $this->arrayField = $arrayField;
        $this->relatedKey = $relatedKey;
        
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
    
    
}

?>