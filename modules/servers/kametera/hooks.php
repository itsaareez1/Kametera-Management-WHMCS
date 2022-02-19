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
use WHMCS\Database\Capsule;
use WHMCS\View\Menu\Item as MenuItem;
// Require any libraries needed for the module to function.
// require_once __DIR__ . '/path/to/library/loader.php';
//
// Also, perform any initialization required by the service's library.

/**
 * Client edit sample hook function.
 *
 * This sample demonstrates making a service call whenever a change is made to a
 * client profile within WHMCS.
 *
 * @param array $params Parameters dependant upon hook function
 *
 * @return mixed Return dependant upon hook function
 */
function hook_kametera_clientedit(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Consider logging or reporting the error.
    }
}

/**
 * Register a hook with WHMCS.
 *
 * add_hook(string $hookPointName, int $priority, string|array|Closure $function)
 */
add_hook('ClientEdit', 1, 'hook_kametera_clientedit');

/**
 * Insert a service item to the client area navigation bar.
 *
 * Demonstrates adding an additional link to the Services navbar menu that
 * provides a shortcut to a filtered products/services list showing only the
 * products/services assigned to the module.
 *
 * @param \WHMCS\View\Menu\Item $menu
 */
add_hook('ClientAreaPrimaryNavbar', 1, function ($menu)
{
    // Check whether the services menu exists.
    if (!is_null($menu->getChild('Services'))) {
        // Add a link to the module filter.
        $menu->getChild('Services')
            ->addChild(
                'Kametera',
                array(
                    'uri' => 'clientarea.php?action=services&module=kametera',
                    'order' => 15,
                )
            );
    }
});
add_hook('ClientAreaPrimarySidebar', 1, function(MenuItem $primarySidebar)
{
//    if (!is_null($primarySidebar->getChild('Service Details Overview'))) {
//             $primarySidebar->removeChild('Service Details Overview');
//    }
   if (!is_null($primarySidebar->getChild('Service Details Actions'))) {
            $primarySidebar->getChild('Service Details Actions')->removeChild('Upgrade/Downgrade Options');
            $primarySidebar->getChild('Service Details Actions')->removeChild('Custom Module Button SS Create');
            $primarySidebar->getChild('Service Details Actions')->removeChild('Custom Module Button SS One Delete');
            $primarySidebar->getChild('Service Details Actions')->removeChild('Custom Module Button SS Two Delete');
            $primarySidebar->getChild('Service Details Actions')->removeChild('Custom Module Button SS Three Delete');
            $primarySidebar->getChild('Service Details Actions')->removeChild('Custom Module Button SS Four Delete');
            $primarySidebar->getChild('Service Details Actions')->removeChild('Custom Module Button SS One Revert');
            $primarySidebar->getChild('Service Details Actions')->removeChild('Custom Module Button SS Two Revert');
            $primarySidebar->getChild('Service Details Actions')->removeChild('Custom Module Button SS Three Revert');
            $primarySidebar->getChild('Service Details Actions')->removeChild('Custom Module Button SS Four Revert');

        }    

});
/**
 * Render a custom sidebar panel in the secondary sidebar.
 *
 * Demonstrates the creation of an additional sidebar panel on any page where
 * the My Services Actions default panel appears and populates it with a title,
 * icon, body and footer html output and a child link.  Also sets it to be in
 * front of any other panels defined up to this point.
 *
 * @param \WHMCS\View\Menu\Item $secondarySidebar
 */
add_hook('ClientAreaSecondarySidebar', 1, function ($secondarySidebar)
{
    // determine if we are on a page containing My Services Actions
    if (!is_null($secondarySidebar->getChild('My Services Actions'))) {

        // define new sidebar panel
        $customPanel = $secondarySidebar->addChild('Kametera');

        // set panel attributes
        // $customPanel->moveToFront()
        //     ->setIcon('fa-user')
        //     ->setBodyHtml(
        //         'Your HTML output goes here...'
        //     )
        //     ->setFooterHtml(
        //         'Footer HTML can go here...'
        //     );

        // define link
        // $customPanel->addChild(
        //         'List Snapshots',
        //         array(
        //             'uri' => 'clientarea.php?action=listSnapshots&module=kametera',
        //             'icon'  => 'fa-list-alt',
        //             'order' => 2,
        //         )
        // );
        // $customPanel->addChild(
        //     'List Disks',
        //     array(
        //         'uri' => 'clientarea.php?action=listDisks&module=kametera',
        //         'icon'  => 'fa-list-alt',
        //         'order' => 2,
        //     )
        // );        

    }
});

add_hook('AdminProductConfigFieldsSave', 1, function($vars) {
    
    kametera_createCommandIdField($vars["pid"]);    
    kametera_createCloneField($vars["pid"]);
    if ($_REQUEST["packageconfigoption"][1] == "on")
    {
        $gid = kametera_productConfigGetID();

        //Call the service's connection test function.
        $clientId = "71593d693646c8bc2353bdca5fd41121";
        $secret = "1b186c2059445013a934fac97a3bf851";
        $serverhostname = "console.kamatera.com";
    
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://" . $serverhostname . "/service/server");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "AuthClientId: {$clientId}",
            "AuthSecret: {$secret}"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($body);
        if ($status == 200)
        {
            $configidDC = kametera_addConfigOption($gid, "datacenters| Data Centers");
            $configidCPU = kametera_addConfigOption($gid, "cpus | CPUs");
            $configidDisk = kametera_addConfigOption($gid, "disks | Primary Disk Size");
            $configidRAM = kametera_addConfigOption($gid, "ram | RAM Type");
            $configidIMAGE = kametera_addConfigOption($gid, "images | Images");
            $configidTRAFFIC = kametera_addConfigOption($gid, "traffic | Traffic");
            $configidNETWORK = kametera_addConfigOption($gid, "networks | Network");
            $configidDisk1 = kametera_addConfigOption($gid, "disk1 | Disk 2 Size (Optional)");
            $configidDisk2 = kametera_addConfigOption($gid, "disk2 | Disk 3 Size (Optional)");
            $configidSnapshots = kametera_addConfigOption($gid, "snapshots | Snapshots");
            $configidBackup = kametera_addConfigOption($gid, "backup | Backup");
            
            //data entry for backup options
            kametera_addConfigOptionSub($configidBackup, "no");
            kametera_addConfigOptionSub($configidBackup, "yes");

            //loop for Snapshots
            for ($i = 0 ; $i <= 4 ; $i++)
            {
                kametera_addConfigOptionSub($configidSnapshots, $i);
            } 
            //loop for data centers
            foreach($data->datacenters as $key => $datacenter)
            {
                kametera_addConfigOptionSub($configidDC, $key . "|" .$datacenter);
            }
            //loop for CPUs
            for ($i = 0 ; $i < count($data->cpu) ; $i++)
            {
                kametera_addConfigOptionSub($configidCPU, $data->cpu[$i]);
            }
            //ramtype and ram loop
            foreach($data->ram as $ramtype => $ram)
            {
                for ($i = 0 ; $i < count($ram) ; $i++)
                {
                    kametera_addConfigOptionSub($configidRAM, $ramtype . $ram[$i] . "|" . $ram[$i] . "MB:" . $ramtype);
                }
            }
            //loop for Disks 0
            for ($i = 0 ; $i < count($data->disk) ; $i++)
            {
                kametera_addConfigOptionSub($configidDisk, $data->disk[$i] . "GB");
            }
            //loop for images
            foreach($data->diskImages as $datacentr => $image)                
            {
                for ($i = 0 ; $i < count($image) ; $i++)
                {
                    kametera_addConfigOptionSub($configidIMAGE, $image[$i]->id . "|" . $datacentr . ":" . $image[$i]->description . " - Minimum Required Disk " . $image[$i]->sizeGB . "GB");
                }
            }
            //loop for traffic
            foreach($data->traffic as $datacentr => $traffic)                
            {
                for ($i = 0 ; $i < count($traffic) ; $i++)
                {
                    kametera_addConfigOptionSub($configidTRAFFIC, $traffic[$i]->id . " | $datacentr: " . $traffic[$i]->info ." :" . $traffic[$i]->id);
                }
            }
            //loop for networks
            $nwts = array();
            foreach($data->networks as $datacentr => $networks)                
            {
                for ($i = 0 ; $i < count($networks) ; $i++)
                {
                    array_push($nwts, $networks[$i]->name);
                }
            }     
            $unique_ntws = array_unique($nwts);
            for ($i = 0 ; $i < count($unique_ntws) ; $i++)
            {
                kametera_addConfigOptionSub($configidNETWORK, $networks[$i]->name);                    
            }
            //loop for Disk 1
            kametera_addConfigOptionSub($configidDisk1, "0GB");
            for ($i = 0 ; $i < count($data->disk) ; $i++)
            {
                kametera_addConfigOptionSub($configidDisk1, $data->disk[$i] . "GB");
            }
            //loop for Disk 2
            kametera_addConfigOptionSub($configidDisk2, "0GB");
            for ($i = 0 ; $i < count($data->disk) ; $i++)
            {
                kametera_addConfigOptionSub($configidDisk2, $data->disk[$i] . "GB");
            }

            kametera_assignProductConfigGroup($vars["pid"], $gid);     
        }
        else
        {
            logModuleCall(
                'kametera',
                __FUNCTION__,
                "An error occured in hookaa bar while executing Kametera.",
                "",
                ""
            );
        }

    }
    else
    {
        logModuleCall(
            'kametera',
            __FUNCTION__,
            "OFF",
            "",
            ""
        );
    }

});

add_hook('AdminAreaFooterOutput', 1, function($vars) {

    return <<<HTML
<script type="text/javascript">

    function form(){
        $("#divModuleSettings").removeClass("hidden");
        $("#kameconfig").next().removeClass("hidden");
        $("#kameconfig").removeClass("hidden");
    }
    $(document).ready(function(){
        if ($("#inputServerGroup").find("option:selected").text().toLowerCase() == "kametera")
        {
            $('<div class="text-center" id="kameconfig"><button class="btn btn-success">Add Configurable Options</button></div><br/>').insertAfter('#divModuleSettings');
            form();
        }
        else
        {
            $('<div class="text-center" id="kameconfig"><button class="btn btn-success">Add Configurable Options</button></div><br/>').insertAfter('#divModuleSettings');
            $("#divModuleSettings").addClass("hidden");
            $("#kameconfig").next().addClass("hidden");
            $("#kameconfig").addClass("hidden");
        }

        $("#inputServerGroup").change(function(){
            if ($(this).find("option:selected").text().toLowerCase() == "kametera")
            {
               form();
            }
            else
            {
                $("#divModuleSettings").addClass("hidden");
                $("#kameconfig").next().addClass("hidden");
                $("#kameconfig").addClass("hidden");
            }
        })
        $("#kameconfig>button").click(function(){
            alert("OK");
        })
    });
   
</script>
HTML;
});

add_hook('ClientAreaProductDetailsOutput', 1, function($service) {
    logModuleCall(
        'kametera',
        __FUNCTION__,
        $service["service"]["id"],
        "",
        ""
    );

    if (isset($service["service"]["subscriptionid"]))
    {
            if ($service["service"]["subscriptionid"] != "")
            {
                $clientId = "71593d693646c8bc2353bdca5fd41121";
                $secret = "1b186c2059445013a934fac97a3bf851";
                $status = kametera_powerStatus($clientId, $secret, $service["service"]["subscriptionid"]);
                $badge = "";
                if ($status == "on")
                {
                    $badge = 'Server Power Status <div class="badge badge-success">' . $status . '</div>';
                }
                else
                {
                    $badge = 'Server Power Status <div class="badge badge-danger">' . $status . '</div>';
                }
                return '<div class="card"><div class="card-body"><div class="row"><div class="col-md-12 text-center" style="magin: 0 auto;">' . $badge . '</div></div></div>';    
            } 
    }
    else
    {
        $badge = 'Server Power Status <div class="badge badge-warning">Getting your server ready. It may take upto 10 minutes.</div>';
        return '<div class="card"><div class="card-body"><div class="row"><div class="col-md-12 text-center" style="magin: 0 auto;">' . $badge . '</div></div></div>';    
    }
});
add_hook('ClientAreaPageCart', 1, function($vars) {

});
add_hook('ClientAreaHeadOutput', 1, function($vars) {
    return <<<HTML
<script type="text/javascript" src="assets/js/kametera.js"></script>
HTML;

});
// add_hook('PreModuleProvisionAddOnFeature', 1, function($vars) {
//     logModuleCall(
//         'kametera',
//         __FUNCTION__,
//         "PreModuleProvisionAddOnFeature",
//         $vars,
//         ""
//     );
// });
// add_hook('OrderPaid', 1, function($vars) {
//     logModuleCall(
//         'kametera',
//         __FUNCTION__,
//         "OrderPaid",
//         $vars,
//         ""
//     );
// });
// add_hook('ClientAreaPageUpgrade', 1, function($vars) {
//     logModuleCall(
//         'kametera',
//         __FUNCTION__,
//         "ClientAreaPageUpgrade",
//         $vars,
//         ""
//     );
// });
// add_hook('AfterProductUpgrade', 1, function($vars) {
//     logModuleCall(
//         'kametera',
//         __FUNCTION__,
//         "AfterProductUpgrade",
//         $vars,
//         ""
//     );
// });

// add_hook('AfterConfigOptionsUpgrade', 1, function($vars) {
//     logModuleCall(
//         'kametera',
//         __FUNCTION__,
//         "AfterConfigOptionsUpgrade",
//         $vars,
//         ""
//     );
// });
add_hook('PreModuleChangePackage', 1, function($vars) {

    //stops execution of ChangePackage function if there are already pending tasks in queue.
        try {
            $data = Capsule::table('tblkamcommands')
            ->where('service_id',  $vars["params"]["model"]["subscriptionid"])
            ->where('status', 'pendingx')->first();

            if (count($data) > 0)
            {
                $op = "";
                $requested_value = "";
                $serverInfo = kametera_getServerInformation($vars["params"]['serverusername'], $vars["params"]['serverpassword'], $vars["params"]["model"]["subscriptionid"]);
                if ($serverInfo->cpu != $vars["params"]["configoptions"]["cpus"])
                {
                    $op = "cpu";
                    $requested_value = $vars["params"]["configoptions"]["cpus"];
                }
                else if ($serverInfo->ram != preg_replace("/[^0-9]/", "", $vars["params"]["configoptions"]["ram"]))
                {
                    $op = "ram";
                    $requested_value = $vars["params"]["configoptions"]["ram"];
                }
                else if ($serverInfo->diskSizes[0] != preg_replace("/[^0-9]/", "", $vars["params"]["configoptions"]["disks"]))
                {
                    $op = "disk0";
                    $requested_value = $vars["params"]["configoptions"]["disks"];
                }
                else if (count($serverInfo->diskSizes) >= 2 && $serverInfo->diskSizes[1] != preg_replace("/[^0-9]/", "", $vars["params"]["configoptions"]["disk1"]) && preg_replace("/[^0-9]/", "", $vars["params"]["configoptions"]["disk1"]) != 0)
                {
                    $op = "disk1";
                    $requested_value = $vars["params"]["configoptions"]["disk1"];
                }
                else if (count($serverInfo->diskSizes) == 3 && $serverInfo->diskSizes[2] != preg_replace("/[^0-9]/", "", $vars["params"]["configoptions"]["disk2"]) && preg_replace("/[^0-9]/", "", $vars["params"]["configoptions"]["disk2"]) != 0)
                {
                    $op = "disk2";
                    $requested_value = $vars["params"]["configoptions"]["disk2"];
                }
                else if (preg_replace("/[^0-9]/", "", $vars["params"]["configoptions"]["disk1"]) == 0)
                {
                    $op = "disk1-delete";
                    $requested_value = $vars["params"]["configoptions"]["disk1"];    
                }
                else if (preg_replace("/[^0-9]/", "", $vars["params"]["configoptions"]["disk2"]) == 0)
                {
                    $op = "disk2-delete";
                    $requested_value = $vars["params"]["configoptions"]["disk2"];    
                }
                else if (preg_replace("/[^0-9]/", "", $vars["params"]["configoptions"]["disk2"]) != 0)
                {
                    $op = "disk2-create";
                    $requested_value = $vars["params"]["configoptions"]["disk2"];                    
                }
                else if (preg_replace("/[^0-9]/", "", $vars["params"]["configoptions"]["disk1"]) != 0)
                {
                    $op = "disk1-create";
                    $requested_value = $vars["params"]["configoptions"]["disk1"];                    
                }
               

                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    $op,
                    $vars["params"],
                    ""
                );
                $pdo = Capsule::connection()->getPdo();
                $pdo->beginTransaction();
                
                try {
                    $statement = $pdo->prepare(
                        'insert into tblkamtasksqueue (operation, requested_value, server_id, status) values (:operation, :requested_value, :server_id, :status)'
                    );
                
                    $statement->execute(
                        [
                            ':operation' => $op,
                            ':requested_value' => $requested_value,
                            ':server_id' => $vars["params"]["model"]["subscriptionid"],
                            ':status' => "pending",
                        ]
                    );
                
                    $pdo->commit();
                } catch (\Exception $e) {
                    logModuleCall(
                        'kametera',
                        __FUNCTION__,
                        $e->getMessage(),
                        "",
                        ""
                    );
                    $pdo->rollBack();
                }

                return array("abortcmd"=>true);
            }
        } catch (\Exception $e) {
            logModuleCall(
                'kametera',
                __FUNCTION__,
                $vars,
                $e->getMessage(),
                $e->getTraceAsString()
            );
        }
});
add_hook('AfterModuleChangePackageFailed', 1, function($vars) {
   return array("failureResponseMessage" => "Operation failed. Try again later in few minutes.");
});
// add_hook('AfterModuleChangePackage', 1, function($vars) {
//     logModuleCall(
//         'kametera',
//         __FUNCTION__,
//         "AfterModuleChangePackage",
//         $vars,
//         ""
//     );
// });

add_hook('ClientAreaPageProductDetails', 1, function($vars) {
    logModuleCall(
        'kametera',
        __FUNCTION__,
        $vars,
        "",
        ""
    );
    return array("username" => "root");
});