<?php

namespace App\Http\Controllers;

use App\ShowPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShowsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($show)
    {
        $url = 'http://api.tvmaze.com/search/shows?q=' . $show;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Connection: Keep-Alive'
        ]);

        $result = curl_exec($ch);
        $shows = json_decode($result);
        $list = [];
        foreach ($shows as $show) {
            $list = [
                'id' => $show->show->id,
                'name' => $show->show->name,
            ];
            return $list;
        }

    }

    public function store(Request $request)
    {
        $userID = $request->userId;
        if ($request->activeStatus == 200) {
            $search = $request->inputObject->filterCriteria;
            $showPref = new  ShowPreference();

            $show = $this->index($search);

            $showPref->user_id = $userID;
            $showPref->show_id = $show->id;

            if ($showPref->save()) {
                $request->activeStatus = 1;
                return $request;
            }

        } elseif ($request->activeStatus == 403) {
            $delete = ShowPreference::where('user_id', $userID)->get();
            $delete->delete();
            $request->activeStatus = 404;
            return $request;
        }

        Log::info('Showing user profile for user: '.$userID);



    }

    public function show()
    {
        return ShowPreference::all();
    }
}
