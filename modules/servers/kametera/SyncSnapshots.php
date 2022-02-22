<?php
/**
 * 
 * Name: Kamtera Management Plugin for WHMCS
 * Version: v1.0.0
 * Description: This plugin connects WHMCS with Kametera, a cloud service provider, and automates all processes.
 * Developed by Arslan ud Din Shafiq
 * Websoft IT Development Solutions (Private) Limited, Pakistan.
 * WhatsApp: +923041280395
 * WeChat: +923041280395
 * Email: itsaareez1@gmail.com
 * Skype: arslanuddin200911
 * 
 */
require_once __DIR__ . '/../../../init.php';
require_once 'kametera.php';
use WHMCS\Database\Capsule;

$conn = file_get_contents(dirname(__FILE__) .'/storage/connection'); 
$server = json_decode($conn);

$clientId = $server->clientId;
$secret = $server->secret;

kametera_sync_snapshots($clientId, $secret);



function kametera_sync_snapshots($clientId, $secret){

    $data = Capsule::table('tblkamcommands')
    ->where('status', 'success')
    ->where(function ($query){
        $query->where('description', 'disk0')
        ->orWhere('description', 'disk1')
        ->orWhere('description', 'disk2');
    })->get();

    if (count($data) > 0)
    {

        foreach($data as $server)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$server->service_id}/snapshots");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
               "AuthClientId: {$clientId}",
               "AuthSecret: {$secret}"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $body = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($status == 200)
            {
                $i = 0;
                $root = json_decode($body)[0];
                while(count($root->child) > 0)
                {
                    $root = $root->child[0];
                    $i++;
                }
                
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    json_decode($body),
                    $i,
                    ""
                );
            }
            else
            {
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    json_decode($body),
                    "",
                    ""
                );
            }
        }
    }
    // if (count($data) > 0)
    // {
    //     foreach ($data as $record)
    //     {
    //         Capsule::table('tblkamtasksqueue')->where('id' , $record->queueid)->update(['status' => 'complete']);
    //     }
    // }
}