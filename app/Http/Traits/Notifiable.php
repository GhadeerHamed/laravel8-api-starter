<?php

namespace App\Http\Traits;


trait Notifiable {

    /**
     * @throws \JsonException
     */
    public function sendNotificationToMobile($title, $message, $userId = null){

        $appID = config('dev_creds.OSG_APP_ID');
        $restApi = config('dev_creds.OSG_REST_API');

        $content = array(
            "en" => $message
        );

        $headings = array(
            "en" => $title
        );

        if ($userId)
            $fields = array(
                'app_id' => $appID,
                'filters' => array(array("field" => "tag", "key" => "userId", "relation" => "=", "value" => $userId)),
                'contents' => $content,
                'headings' => $headings,
            );
        else
            $fields = array(
                'app_id' => $appID,
                'filters' => array(array("field" => "tag", "key" => "type", "relation" => "=", "value" => "mobile")),
                'contents' => $content,
                'headings' => $headings,
            );

        $fields = json_encode($fields, JSON_THROW_ON_ERROR);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic '.$restApi
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response);
        if (isset($result->errors) && $result->errors)
            Throw new \Exception($result->errors[0]);

//        return $response;
    }

    /**
     * @throws \JsonException
     */
    public function sendNotificationToWeb(){
        $appID = config('dev_creds.OSG_APP_ID');
        $restApi = config('dev_creds.OSG_REST_API');

        $content = array(
            "en" => __('notification.new_order'),
        );

        $headings = array(
            "en" =>   config('app.title')
        );

        $fields = array(
            'app_id' => $appID,
            'filters' => array(array("field" => "tag", "key" => "admin", "relation" => "=", "value" => "true")),
            'contents' => $content,
            'headings' => $headings,
            'url' => route('admin.orders.index'),
            'chrome_web_image' =>  asset('custom/logo-icon.png') ,
        );

        $fields = json_encode($fields, JSON_THROW_ON_ERROR);
//        print("\nJSON sent:\n");
//        print($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic '.$restApi
        ));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);

        $response = curl_exec($ch);
        curl_close($ch);

    }

}

