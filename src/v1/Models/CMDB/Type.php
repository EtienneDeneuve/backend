<?php  
/**
 * FusionSuite - Frontend
 * Copyright (C) 2022 FusionSuite
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace App\v1\Models\CMDB;

use Illuminate\Database\Eloquent\Model as Model;
use App\v1\Controllers\Rule as Rule;
use Illuminate\Events\Dispatcher as Dispatcher;

class Type extends Model {  

  protected $appends = [
    'properties',
    'propertygroups'
  ];
  protected $visible = [
    'id', 
    'name',
    'modeling',
    'properties',
    'propertygroups',
    'created_at',
    'updated_at'
  ];
  protected $hidden = [];
  protected $with = [
    'properties'
  ];

  protected static function booted()
  {
    static::retrieved(function ($type)
    {
      // This code works ;)
      // $type->name = "xxxxx";
    });

    static::saving(function ($type)
    {
      // $type = \App\v1\Controllers\Rule::runRules($type);
      // This code works ;)
      // $type->name = "xxxxx";
    });

    static::saved(function ($type)
    {
      // This code works ;)
      // $type->name = "xxxxx";
    });
  }

  public function getPropertiesAttribute()
  {
    return [];
  }

  public function getPropertygroupsAttribute()
  {
    return $this->propertygroups()->get();
  }


  public function properties()
  {
    return $this->belongsToMany('App\v1\Models\CMDB\Property')->withTimestamps();
  }

  public function propertygroups()
  {
    return $this->hasMany('App\v1\Models\CMDB\Propertygroup');
  }

}
