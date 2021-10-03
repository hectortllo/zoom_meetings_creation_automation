<?php

namespace App\Console\Commands;

use App\Http\Controllers\Telegram\TelegramController;
use App\Http\Controllers\Zoom\MeetingController;
use Illuminate\Console\Command;
use Carbon\Carbon;

class createZoomMeetings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:zoommeeting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for creating a new zoom meeting and send the links meeting to telegram channel';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now()->endOfDay();
        $now->hour(20);
        $now->minute(00);
        $now->second(00);
        $start_time = Carbon::create($now)->format('Y-m-d\TH:i:s');

        $meeting = new MeetingController();
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->request->add(['topic' => 'Oración ' . $now->format('d-m-Y')]);
        $request->request->add(['start_time' => $start_time]);
        $request->request->add(['timezone' => 'America/Guatemala']);
        $request->request->add(['agenda' => 'Oración de los jueves, iglesia Casa de mi Padre']);

        $response = $meeting->create($request);
        $data = $response['data'];
        
        $new_data = self::formatData($data, $now->format('H:i:s'));
        self::sendMessage($new_data);
    }

    private static function formatData($data, $start_time) {
        $topic = $data['topic'];
        $time = $start_time;
        $join_url = $data['join_url'];
        $id = $data['id'];
        $timezone = $data['timezone'];

        $join_url = str_replace("\\", "", $join_url);
        $timezone = str_replace("\\", "", $timezone);
        

        $new_data = array(
            'topic' => $topic,
            'time' => $time,
            'join_url' => $join_url,
            'id' => $id,
            'timezone' => $timezone
        );

        return $new_data;
    }

    private static function sendMessage($data) {
        $message = '';
        $message .= 'buenos días hermanos, les envío el link para la reunión de hoy' . PHP_EOL;

        $message .= PHP_EOL . 'Topic: ' . $data['topic'] . PHP_EOL;
        $message .= 'Time: ' . $data['time'] . " " . $data['timezone'] . PHP_EOL;

        $message .= PHP_EOL . 'Para unirse dar click aquí: ' . $data['join_url'] . PHP_EOL;

        $message .= PHP_EOL . 'Meeting ID: ' . $data['id'];

        $chatId = env('TELEGRAM_CHAT_ID', '');
        TelegramController::sendMessage($chatId, $message);

        echo 'MENSAJE ENVIADO CORRECTAMENTE' . PHP_EOL;
    }
}
