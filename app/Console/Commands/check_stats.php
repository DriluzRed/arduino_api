<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Statistic;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class check_stats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica los datos de las estadísticas cada 5 minutos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $current_minute = date('i');
        $five_minutes_ago = Carbon::now()->subMinutes(5);
        $five_minutes_ago_minute = $five_minutes_ago->format('i');
        $statistic = Statistic::whereBetween('created_at', [$five_minutes_ago_minute, $current_minute])
        ->first();
        $commandReceived = '';
        if($statistic){
            if($statistic->ground_humidity < 20 || $statistic->air_humidity < 20){
                    $this->info('La humedad del suelo o del aire es menor a 20% - Enviando comando al arduino');
                    $command = Command::where('command', $commandReceived)->first();
                    if($command) {
                        $this->sendCommandToArduino($command->command);
                    } else {
                        $this->info('El comando recibido no está en la tabla Commands');
                    }
            }
        }
    }
    

    public function sendCommandToArduino($command){
        
        $arduinoIp = '';
        $url = 'http://'.$arduinoIp.'/arduino/'.$command;
        
        $client = new Client();
        try {
            $response = $client->request('GET', $url);
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

