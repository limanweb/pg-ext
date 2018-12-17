<?php 

namespace Limanweb\PgExt\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    
    use Concerns\HasArrayRelationships;
    use Concerns\PgTypeCastable;
    
}
