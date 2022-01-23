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
namespace App\v1\Controllers\CMDB;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class Item
{

  use \App\v1\Read;

  /**
   * @api {get} /v1/cmdb/types/:typeid/items Get all items of CMDB with type defined
   * @apiName GetCMDBItems
   * @apiGroup CMDBItems
   * @apiVersion 1.0.0-draft
   *
   * @apiUse AutorizationHeader
   *     
   * @apiSuccess {Integer}  id      The id of the item.
   * @apiSuccess {String}   name    The name of the item.
   * 
   * @apiSuccessExample {json} Success-Response:
   * HTTP/1.1 200 OK
   * [
   *   {
   *     "id": 45,
   *     "name": "LP-000345",
   *     "created_at": "2020-07-20 14:30:45",
   *     "updated_at": null,
   *     "properties": [
   *       {
   *         "id": 3,
   *         "name": "Serial number",
   *         "valuetype": "string",
   *         "unit": null,
   *         "listvalues": [],
   *         "value": "gt43bf87d23d",
   *         "byfusioninventory": true
   *       },
   *       {
   *         "id": 4,
   *         "name": "Model",
   *         "valuetype": "string",
   *         "unit": null,
   *         "listvalues": [],
   *         "value": "Latitude E7470",
   *         "byfusioninventory": true
   *       },
   *       {
   *         "id": 5,
   *         "name": "Manufacturer",
   *         "valuetype": "string",
   *         "unit": null,
   *         "listvalues": [],
   *         "value": "Dell",
   *         "byfusioninventory": true
   *       }
   *     ]
   *   }
   * ]
   *
   */
  public function getAll(Request $request, Response $response, $args): Response
  {
    $paramsQuery = $request->getQueryParams();
    $pagination = $this->paramPagination($paramsQuery);

    $params = $this->manageParams($request);

    $items = \App\v1\Models\CMDB\Item //::ofWhere($params)
      ::ofSort($params)->where('type_id', $args['id'])
      ->with('properties:id,name,valuetype,unit', 'properties.listvalues');

    $items = $this->paramFilters($paramsQuery, $items);
    // Example filter on property value
    // $items->whereHas('properties', function($q) {
    //   $q->where('item_property.value', 'VirtualBox');
    // });
    $totalCnt = $items->count();
    $items->skip(($params['skip'] * $params['take']))->take($params['take']);
    $response->getBody()->write($items->get()->toJson());
    $response = $response->withAddedHeader('X-Total-Count', $totalCnt);
    $response = $response->withAddedHeader('Link', $this->createLink($request, $pagination, $totalCnt));
    return $response->withHeader('Content-Type', 'application/json');
  }



  public function getOne(Request $request, Response $response, $args): Response
  {
    $item = \App\v1\Models\CMDB\Item::with('properties:id,name,valuetype,unit', 'properties.listvalues')
      ->find($args['id'])->makeVisible(['propertygroups']);
    if (is_null($item))
    {
      throw new \Exception("This item has not be found", 404);
    }
    $response->getBody()->write($item->toJson());
    return $response->withHeader('Content-Type', 'application/json');
  }


  /**
   * @api {post} /v1/cmdb/types/:typeid/items Create a new item
   * @apiName PostCMDBItems
   * @apiGroup CMDBItems
   * @apiVersion 1.0.0-draft
   *
   * @apiUse AutorizationHeader
   *     
   * @apiSuccess {String}    name                    The name of the item.
   * @apiSuccess {Object[]}  properties              List of properties
   * @apiSuccess {Integer}   properties.property_id  The id of the property.
   * @apiSuccess {String[]}  properties.value        The value of the property for the item.
   * 
   * @apiParamExample {json} Request-Example:
   * {
   *   "name": "LP-000345",
   *   "properties: [
   *     {
   *       "property_id": 3,
   *       "value": "gt43bf87d23d"
   *     },
   *     {
   *       "property_id": 8,
   *       "value": "Latitude E7470"
   *     },
   *     {
   *       "property_id": 5,
   *       "value": "Dell"
   *     }
   *   ]
   * } 
   * 
   * @apiSuccessExample {json} Success-Response:
   * HTTP/1.1 200 OK
   * {
   *   "id":10
   * }
   * 
   * @apiErrorExample {json} Error-Response:
   * HTTP/1.1 400 Bad Request
   * {
   *   "status: "error",
   *   "message": "The Name is required"
   * }
   * 
   */
  public function postItem(Request $request, Response $response, $args): Response
  {
    $token = $request->getAttribute('token');

    $data = json_decode($request->getBody());

    // Validate the data format
    $dataFormat = [
      'name' => 'required'
    ];
    \App\v1\Common::validateData($data, $dataFormat);
    // validate for each properties
    if (property_exists($data, 'properties'))
    {
      foreach ($data->properties as $property)
      {
        $dataFormat = [
          'property_id' => 'required|integer|min:1',
          'value'       => 'required'
        ];
        \App\v1\Common::validateData($property, $dataFormat);
      }
    }


    // TODO run rules for rewrite value
    // $data = $this->runRules($data);
   

    $item = new \App\v1\Models\CMDB\Item;
    $item->name = $data->name;
    $item->type_id = $args['id'];
    $item->owner_user_id = 0;
    $item->owner_group_id = 0;
    $item->state_id = 0;
    $item->save();

    if (property_exists($data, 'properties'))
    {
      foreach ($data->properties as $property)
      {
        $item->properties()->attach($property->property_id, ['value' => $property->value]);
      }
    }

    $response->getBody()->write(json_encode(["id" => intval($item->id)]));
    return $response->withHeader('Content-Type', 'application/json');
  }

  private function runRules($data)
  {
    $ruler   = new \Hoa\Ruler\Ruler();
    $context = new \Hoa\Ruler\Context();

    // prepare context
    $context['name'] = $data->name;
    foreach ($data->properties as $property)
    {
      $prop = \App\v1\Models\CMDB\Property::find($property->property_id)->get();
      $context[$prop->name] = $property->value;
    }

    // get all rules 
    $rules = \App\v1\Models\Rule::where('type', 'rewritefield')->get();
    foreach ($rules as $rule)
    {
      if (!is_null($rule->serialized) && !empty($rule->serialized))
      {
        // TODO seems a problem with serialized, or perhaps the data in DB are not rigth
        $model = unserialize($rule->serialized);
        // $model = \Hoa\Ruler\Ruler::interpret("name = 'test'");

        if ($ruler->assert($model, $context)) {
          // todo rewrite
          // echo "rewrited !!!!\n";
          $data->name = "rewriten";
        }
      }
    }

    return $data;
  }
  
}
