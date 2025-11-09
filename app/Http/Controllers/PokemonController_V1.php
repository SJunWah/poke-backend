<?php

namespace App\Http\Controllers;

use App\Traits\HttpResponse;
use App\Http\Requests\GetList;
use App\Models\Pokemon;

class PokemonController_V1 extends Controller
{
    use HttpResponse;
    /**
     * @param  GetList  $request
     * @return \Illuminate\Http\Response
     */
    public function getList(GetList $request)
    {
        try {
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 20);
            $sort = $request->sort ?? 'created_at';
            $order = $request->order ?? 'asc';
            $name = $request->query('name', null);
            $result = Pokemon::getList($page, $limit, $sort, $order, $name);
            return self::success($result, "Fetched Pokemon successfully");

        } catch (\Exception $e) {
            return self::error($e, "Something went wrong, please contact support", 400);
        }

    }
}
