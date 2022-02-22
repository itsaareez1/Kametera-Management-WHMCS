
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

kametera_sync_tables();
kametera_sync_commands($clientId, $secret);
kametera_deletePostCreatedSnapshot($clientId, $secret);

function kametera_sync_tables(){
    $data = Capsule::table('tblkamtasksqueue')
    ->join('tblkamcommands', 'tblkamtasksqueue.id', '=' , 'tblkamcommands.queueid')
    ->where('tblkamtasksqueue.status', 'pending')
    ->where('tblkamcommands.status', 'success')->get();

    if (count($data) > 0)
    {
        foreach ($data as $record)
        {
            Capsule::table('tblkamtasksqueue')->where('id' , $record->queueid)->update(['status' => 'complete']);
        }
    }
}

function kametera_sync_commands($clientId, $secret){

    $data = Capsule::table('tblkamcommands')
    ->where('status', 'pendingx')->get();

    if (count($data) > 0)
    {
        foreach ($data as $task){
            $status = kametera_getCommandStatus($clientId, $secret, $task->command_id);
            if ($status == "complete")
            {
                try {
                    // if (strpos($task->description, "-ss") !== false)
                    // {
                    //     $clientId = explode("-",$task->description);
                    //     $command = 'AddBillableItem';
                    //     $postData = array(
                    //         'clientid' =>  $clientId[0],
                    //         'description' => 'Snapshot ' . kametera_alreadyCreatedSnapshots($clientId, $secret, $task->service_id) + 1,
                    //         'unit' => 'hours',
                    //         'amount' => '0.10',
                    //         'invoiceaction' => 'nextinvoice'
                    //     );
                    //     $res = localAPI($command, $postData);

                    //     Capsule::table('tblkamcommands')
                    //     ->where('id', $task->id)
                    //     ->update(
                    //         [
                    //             'description' => $clientId[1] . "-snapshot",
                    //             'status' => 'success',
                    //         ]
                    //     );                
                    // }
                    // else
                    // {
                        Capsule::table('tblkamcommands')
                        ->where('id', $task->id)
                        ->update(
                            [   
                                'status' => 'success',
                            ]
                        );   

                        if ($task->queueid != null)
                        {
                            Capsule::table('tblkamtasksqueue')
                            ->where('id', $task->queueid)
                            ->update(
                                [   
                                    'status' => 'complete',
                                ]
                            );       
                        }
                          
                        
                    //}
                } catch (\Exception $e) {
                    logModuleCall(
                        'kametera',
                        __FUNCTION__,
                        $vars,
                        $e->getMessage(),
                        $e->getTraceAsString()
                    );
                }
            }
        }
    }
    $records = Capsule::table('tblkamtasksqueue')
    ->where('status', 'pending')->get()->unique('server_id');

    if (count($records) > 0)
    {
        foreach ($records as $task){
            if ($task->operation == "cpu")
            {
                $commandId = kametera_changeCPU($clientId, $secret, $task->server_id, $task->requested_value);
            }
            else if ($task->operation == "ram")
            {
                $commandId = kametera_changeRAM($clientId, $secret, $task->server_id, preg_replace("/[^0-9]/", "", $task->requested_value));
            }
            else if ($task->operation == "disk0")
            {
                kametera_resizeHardDisk($clientId, $secret, $task->server_id, preg_replace("/[^0-9]/", "", $task->requested_value), 0, 1);
            }
            else if ($task->operation == "disk1-delete")
            {
                 $commandId = kametera_removeDisk($clientId, $secret, $task->server_id, 1);
            }
            else if ($task->operation == "disk2-delete")
            {
                $commandId = kametera_removeDisk($clientId, $secret, $task->server_id, 2);
            }
            else if ($task->operation == "disk1-create")
            {
                $commandId = kametera_addNewDisk($clientId, $secret, $task->server_id, preg_replace("/[^0-9]/", "", $task->requested_value));
            }
            else if ($task->operation == "disk2-create")
            {
                $commandId = kametera_addNewDisk($clientId, $secret, $task->server_id, preg_replace("/[^0-9]/", "", $task->requested_value));
            }
            else if ($task->operation == "disk1")
            {
                $commandId = kametera_resizeHardDisk($clientId, $secret, $task->server_id, preg_replace("/[^0-9]/", "", $task->requested_value), 1, 1);
            }
            else if ($task->operation == "disk2")
            {
                $commandId = kametera_resizeHardDisk($clientId, $secret, $task->server_id, preg_replace("/[^0-9]/", "", $task->requested_value), 2, 1);
            }
            else if ($task->operation == "create-ss")
            {
                // $number_of_existing_ss_on_kameterea = kametera_alreadyCreatedSnapshots($clientId, $secret, $task->serverId);
                // $allowed_snapshots = explode("-",$task->requested_value)[2];
                // logModuleCall(
                //     'kametera',
                //     __FUNCTION__,
                //     "$number_of_existing_ss_on_kameterea: " . $number_of_existing_ss_on_kameterea,
                //     "$allowed_snapshots: " . $allowed_snapshots,
                //     $e->getTraceAsString()
                // );
                //  if ($number_of_existing_ss_on_kameterea >= 0 && $number_of_existing_ss_on_kameterea < 5 && $allowed_snapshots > $number_of_existing_ss_on_kameterea)
                // {
                //     $commandId = kametera_createSnapshot($clientId, $secret, $task->serverId, "");
                // }
            }
            else if ($task->operation == "delete-ss")
            {
                
            }
            $pdo = Capsule::connection()->getPdo();
            $pdo->beginTransaction();
            $status = "";
            if ($commandId["message"] == "success")
            {
                $status = "pendingx";
            }
            else
            {
                $status = $commandId["message"];
            }
            try {

                    $statement = $pdo->prepare(
                        'insert into tblkamcommands (service_id, command_id , status, description, queueid) values (:service_id, :command_id, :status, :description, :queueid)'
                    );
                
                    $statement->execute(
                        [
                            ':service_id' => $task->server_id,
                            ':command_id' => $commandId["code"],
                            ':status' => $status,
                            ':description' => $task->operation,
                            ':queueid' => $task->id
                        ]
                    );
                
                    $pdo->commit();    

                return $commandId["message"];
            } catch (\Exception $e) {
                echo "Uh oh! {$e->getMessage()}";
                $pdo->rollBack();
            }
        }
    }
}

function kametera_deletePostCreatedSnapshot($clientId, $secret)
{
    $disk0 = Capsule::table('tblkamcommands')
    ->where('status', 'success')
    ->where('description', 'disk0')->first();

    $disk1 = Capsule::table('tblkamcommands')
    ->where('status', 'success')
    ->where('description', 'disk1')->first();

    $disk2 = Capsule::table('tblkamcommands')
    ->where('status', 'success')
    ->where('description', 'disk2')->first();

    if (count($disk0) > 0)
    {
        kametera_deleteAllSS($clientId, $secret, $disk0->service_id);
        Capsule::table('tblkamcommands')
        ->where('id', $disk0->id)
        ->update(
            [   
                'description' => 'disk0-ok',
            ]
        );
    }
    else if (count($disk1) > 0)
    {
        kametera_deleteAllSS($clientId, $secret, $disk1->service_id);
        Capsule::table('tblkamcommands')
        ->where('id', $disk1->id)
        ->update(
            [   
                'description' => 'disk1-ok',
            ]
        );
    }
    else if (count($disk2) > 0)
    {
        kametera_deleteAllSS($clientId, $secret, $disk2->service_id);
        Capsule::table('tblkamcommands')
        ->where('id', $disk2->id)
        ->update(
            [   
                'description' => 'disk2-ok',    
            ]
        );
    }
}