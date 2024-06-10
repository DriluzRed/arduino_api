<?php

namespace App\Http\Controllers;

use App\Models\Statistic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Command;

class StatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $curdate = Carbon::today();
        $statistics = Statistic::where('created_at', '>=', $curdate)->get();
        return response()->json($statistics);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $statistic = new Statistic();
        $statistic->temperature = $request->temperature;
        $statistic->ground_humidity = $request->ground_humidity;
        $statistic->air_humidity = $request->air_humidity;
        $statistic->save();
        return response()->json('Estadística guardada', 200);
    }
    
    public function getData(Request $request){
        $desde = $request->from . ' 00:00:00';
        $hasta = $request->to . ' 23:59:59';
        $statistics = Statistic::where('created_at', '>=', $desde)
            ->where('created_at', '<=', $hasta)
            ->get();
        if($statistics->isEmpty()){
            return response()->json('No hay datos', 404);
        }
        return response()->json($statistics, 200);
    }

    public function sendCommand(){
        $arduinoIp = '';
        $command = Command::where('type','regar')->first();
        $url = 'http://'.$arduinoIp.'/arduino/'.$command->command;
        $client = new Client();
        try {
            $response = $client->request('POST', $url);
            if ($response->getStatusCode() == 200) {
                return response()->json(['message' => 'Comando enviado correctamente', 'response' => $response->getBody()->getContents()]);
            } else {
                return response()->json(['message' => 'Error al enviar el comando', 'response' => $response->getBody()->getContents()], $response->getStatusCode());
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al enviar el comando', 'error' => $e->getMessage()], 500);
        }
    }
}
