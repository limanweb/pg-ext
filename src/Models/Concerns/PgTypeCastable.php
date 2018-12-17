<?php

namespace Limanweb\PgExt\Models\Concerns;

use Limanweb\PgExt\Support\PgHelper;

trait PgTypeCastable
{

    /**
     * Cast an attribute to a native PHP types.
     * Owerride native Model::castAttribute
     *
     * Added cast types: 'pg_array'
     *
     * @param  string   $key
     * @param  mixed    $value
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        $ret = $value;
        if (!is_null($value)) {
            switch ($this->getCastType($key)) {
                case 'pg_array':
                    $ret = PgHelper::fromPgArray($value);
                    break;
                default:
                    $ret = parent::castAttribute($key, $value);
            }
        }
        return $ret;
    }

    /**
     * Cast an attribute from native PHP types to custom and PjstgreSQL types.
     *
     * Added cast types: 'pg_array', 'pg_point', 'pg_custom_dd_mm'
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);

        if (isset($this->casts[$key])) {
            switch ($this->getCastType($key)) {
                case 'pg_array':
                    $this->attributes[$key] = PgHelper::toPgArray($value);
                    break;
                default:
                    break;
            }
        }
        return $this;
    }
}
