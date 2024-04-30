<?php

namespace App\Mail;

use App\Models\RecordingRequest;
use App\Models\CamerasProxy;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecordingVideoMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $message, $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($recordingRequestId, $user)
    {
        $recordingRequest = RecordingRequest::find($recordingRequestId);

        $camera_proxy = CamerasProxy::with(['camera'])
                                        ->find($recordingRequest->id_camera);

        $this->message = 'El usuario  ' . $user->email . ' ha compartido contigo el video grabado de la cámara ' . $camera_proxy->camera->name . ' el video estará disponible hasta ' . Carbon::createFromFormat('Y-m-d H:i:s',  $recordingRequest->authorized_to)->format('d-m-Y H:i:s');
        $token = $user->createToken('authToken');
        $token->token->expires_at = now()->addHours(24);
        $token->token->save();
        $this->url = env('PSIMV2_URL').'/#/recording_request_stream?id='.$recordingRequestId.'&access_token='.$token->accessToken;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('bbva_video')
            ->subject('Respuesta de Solicitud de Grabación')
            ->with([
                'msg' => $this->message,
                'url' => $this->url
            ]);
    }

}
