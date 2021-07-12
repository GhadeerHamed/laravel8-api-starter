<?php

namespace App\Http\Traits;

trait FcmNotifiable
{
    /**
     * @throws \JsonException
     */
    public function sendToClient($deviceToken, $title, $body)
    {
        if (!empty($deviceToken) && $deviceToken !== 'NULL') {
            $deviceToken = json_decode(json_encode($deviceToken, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

            $FIREBASE_API_KEY = env('FCM_SERVER_KEY');
            $notification = array('title' => $title, 'body' => $body, 'sound' => 'default', 'badge' => '1');
            $fields = array('registration_ids' => $deviceToken, 'notification' => $notification, 'priority' => 'high', 'data' => $notification);

            $headers = array(
                'Content-Type' => 'application/json',
                'Authorization' => 'key=' . $FIREBASE_API_KEY,
            );


            $url = 'https://fcm.googleapis.com/fcm/send';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields, JSON_THROW_ON_ERROR));
            $result = curl_exec($ch);
            curl_close($ch);
        }
    }

    public function sendToTopic($title, $body, $action, $id, $platform, $token): void
    {
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $data = [
            'title' => $title,
            'fromAPI' => true,
            'sound' => true,
            'body' => $body,
            'action' => $action,
            'record_id' => $id
        ];

        if (is_array($token)) {
            $fcmNotification = [
                'registration_ids' => $token,
                'data' => $data,
                'notification' => $data,
                'content_available' => true,
                'priority' => "high"
            ];
        } else {
            if ($platform == 1) { // android
                $fcmNotification = [
                    'to' => $token, //single token
                    'data' => $data,
                    'content_available' => true,
                    'mutable-content' => true,
                    'priority' => "high",
                ];
            } else
                $fcmNotification = [
                    'to' => $token, //single token
                    'data' => $data,
                    'notification' => $data,
                    'content_available' => true,
                    'mutable_content' => true,
                    'priority' => "high",
                ];
        }

        $headers = [
            'Authorization: key=' . env('FCM_SERVER_KEY'),
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
        curl_close($ch);

    }
}
