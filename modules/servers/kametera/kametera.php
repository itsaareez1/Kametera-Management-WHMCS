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
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
// Require any libraries needed for the module to function.
// require_once __DIR__ . '/path/to/library/loader.php';
//
// Also, perform any initialization required by the service's library.

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related abilities and
 * settings.
 *
 * @see https://developers.whmcs.com/provisioning-modules/meta-data-params/
 *
 * @return array
 */
function kametera_MetaData()
{
    return array(
        'DisplayName' => 'Kametera',
        'APIVersion' => '1.1', // Use API Version 1.1
        'RequiresServer' => true, // Set true if module requires a server to work
        'DefaultNonSSLPort' => '1111', // Default Non-SSL Connection Port
        'DefaultSSLPort' => '1112', // Default SSL Connection Port
        'ServiceSingleSignOnLabel' => 'Login to Panel as User',
        'AdminSingleSignOnLabel' => 'Login to Panel as Admin',
    );
}
function kametera_setPowerStatus($clientId, $secret, $serverId, $status)
{
    try {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$serverId}/power");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "AuthClientId: {$clientId}",
            "AuthSecret: {$secret}",
            "Content-Type: application/x-www-form-urlencoded"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'power' => $status
        )));
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status == 200)
        {

            logModuleCall(
                'kametera',
                __FUNCTION__,
                json_decode($body),
                "",
                ""
            );
            return json_decode($body);
        }
        else if (str_contains(json_decode($body)->errors[0]->info, "already"))
        {
            logModuleCall(
                'kametera',
                __FUNCTION__,
                "already",
                "",
                ""
            );
    
            return 1;
        }
        else
        {
            logModuleCall(
                'kametera',
                __FUNCTION__,
                "Here",
                "",
                ""
            );
    
            return 0;
        }

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}
/**
 * This function fetches server information
 */
function kametera_getServerInformation($clientId, $secret, $serverId)
{

    try {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$serverId}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "AuthClientId: {$clientId}",
            "AuthSecret: {$secret}"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response = json_decode($body);

        if ($status == 200)
        {
            return $response;
        }
        else
        {
            return $response->errors[0]->info;
        }

    }
    catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        return $e->getMessage();
    }
}
/**
 * This function fetches the current power status of Server
 */
function kametera_powerStatus($clientId, $secret, $serverId){

    try {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$serverId}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "AuthClientId: {$clientId}",
            "AuthSecret: {$secret}"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response = json_decode($body);
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $response->power,
            "",
            ""
        );
        if ($status == 200)
        {
            return $response->power;
        }
        else
        {
            return $response->errors[0]->info;
        }

    }
    catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        return $e->getMessage();
    }
}
/**
 * 
 * This function creates Kametera Product Configuration and response contains id of inserted record.
 * 
 */
function kametera_productConfigGetID(){
    try {
        $gid = Capsule::connection()->transaction(
            function ($connectionManager)
            {
                /** @var \Illuminate\Database\Connection $connectionManager */
                $gid = $connectionManager->table('tblproductconfiggroups')->insertGetId(
                    [
                        'id' => null,
                        'name' => "Kametera",
                        'description' => "",
                    ]
                ); 
                return $gid;                  
            }
        );

        return $gid;  

    } catch (\Exception $e) {
        echo "Uh oh! Inserting didn't work, but I was able to rollback. {$e->getMessage()}";
    }
}
/**
 * This function assigns custom configuration option group to products
 */
function kametera_assignProductConfigGroup($pid, $gid)
{
    $pdo = Capsule::connection()->getPdo();
    $pdo->beginTransaction();

    try {
        $statement = $pdo->prepare(
            'INSERT INTO `tblproductconfiglinks`(`id`, `gid`, `pid`) VALUES(:id, :gid, :pid)'
        );

        $statement->execute(
            [
                'id' => null,
                'gid' => $gid,
                'pid' => $pid
            ]
        );

        $pdo->commit();

    } catch (\Exception $e) {
        echo "Uh oh! {$e->getMessage()}";
        $pdo->rollBack();
    }
}
/**
 * 
 * this function custom configuration option under each product group
 * 
 */
function kametera_addConfigOption($gid, $name)
{
    $pdo = Capsule::connection()->getPdo();
    $pdo->beginTransaction();

    try {
        $statement = $pdo->prepare(
            'INSERT INTO `tblproductconfigoptions`(`gid`, `optionname`, `optiontype`, `qtyminimum`, `qtymaximum`, `order`, `hidden`) VALUES(:gid, :optionname, :optiontype, :qtyminimum, :qtymaximum, :order, :hidden)'
        );

        $statement->execute(
            [
                ':gid' => $gid,
                ':optionname' => $name,
                ':optiontype' => 1,
                ':qtyminimum' => 0,
                ':qtymaximum' => 0,
                ':order' => 0,
                ':hidden' => 0
            ]
        );
        $configid = $pdo->lastInsertId();
        $pdo->commit();

        return $configid;

    } catch (\Exception $e) {
        echo "Uh oh! {$e->getMessage()}";
        $pdo->rollBack();
    }

}
/**
 * 
 * this function adds options for each configurable option
 * 
 */
function kametera_addConfigOptionSub($configid, $optionname)
{
    $pdo = Capsule::connection()->getPdo();
    $pdo->beginTransaction();

    try {
        $statement = $pdo->prepare(
            'INSERT INTO tblproductconfigoptionssub VALUES(:id, :configid, :optionname, :sortorder, :hidden)'
        );

        $statement->execute(
            [
                ':id' => null,
                ':configid' => $configid,
                ':optionname' => $optionname,
                ':sortorder' => 0,
                ':hidden' => 0
            ]
        );
        $id = $pdo->lastInsertId();
        $pdo->commit();
        return $id;

    } catch (\Exception $e) {
        echo "Uh oh! {$e->getMessage()}";
        $pdo->rollBack();
    }
}
/**
 * 
 * this function adds custom field to save command id
 * 
 */
function kametera_createCommandIdField($pid)
{
   
    try {

        $commandfields = Capsule::table("tblcustomfields")
        ->where("fieldname", "Command ID")
        ->where("adminonly", "on")
        ->where("type", "product")->first();

        if (count($commandfields) < 1)
        {
            $pdo = Capsule::connection()->getPdo();
            $pdo->beginTransaction();
        
            try {
                $statement = $pdo->prepare(
                    'INSERT INTO tblcustomfields (type, relid, fieldname, fieldtype, adminonly) VALUES (:type, :relid, :fieldname, :fieldtype, :adminonly)'
                );
        
                $statement->execute(
                    [
                        ':type' => "product", 
                        ':relid' => (int) $pid, 
                        ':fieldname' => "Command ID",
                        ':fieldtype' => "text",
                        ':adminonly' => "on"
                    ]
                );
                $fieldid = $pdo->lastInsertId();
                $pdo->commit();

                return $fieldid;
        
            } catch (\Exception $e) {
                echo "Uh oh! {$e->getMessage()}";
                $pdo->rollBack();
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    $e->getMessage(),
                    "",
                    ""
                );
            }
    
        }
        else
        {
            return $commandfields->id;
        }

    } catch (Exception $e) {

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }


}
/**
 * 
 * this function adds custom field to allow clone
 * 
 */
function kametera_createCloneField($pid)
{
   
    try {

        $commandfields = Capsule::table("tblcustomfields")
        ->where("fieldname", "Server ID")
        ->where("showorder", "on")
        ->where("type", "product")->first();

        if (count($commandfields) < 1)
        {
            $pdo = Capsule::connection()->getPdo();
            $pdo->beginTransaction();
        
            try {
                $statement = $pdo->prepare(
                    'INSERT INTO tblcustomfields (type, relid, fieldname, fieldtype, showorder) VALUES (:type, :relid, :fieldname, :fieldtype, :showorder)'
                );
        
                $statement->execute(
                    [
                        ':type' => "product", 
                        ':relid' => (int) $pid, 
                        ':fieldname' => "Server ID",
                        ':fieldtype' => "text",
                        ':showorder' => "on",
                    ]
                );
                $fieldid = $pdo->lastInsertId();
                $pdo->commit();
        
            } catch (\Exception $e) {
                echo "Uh oh! {$e->getMessage()}";
                $pdo->rollBack();
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    $e->getMessage(),
                    "",
                    ""
                );
            }
    
        }

    } catch (Exception $e) {

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }


}
/**
 * 
 * this function saves value for command id
 * 
 */
function kametera_saveCommandIdField($fieldid, $service_id, $commandID)
{
   
    try {

        $commandfields = Capsule::table("tblcustomfieldsvalues")
        ->where("fieldid", $fieldid)
        ->where("relid", $service_id)->first();

        if (count($commandfields) < 1)
        {
            $pdo = Capsule::connection()->getPdo();
            $pdo->beginTransaction();
        
            try {
                $statement = $pdo->prepare(
                    'INSERT INTO tblcustomfieldsvalues (fieldid, relid, value) VALUES (:fieldid, :relid, :value)'
                );
        
                $statement->execute(
                    [
                        ':fieldid' => $fieldid, 
                        ':relid' => $service_id, 
                        ':value' => $commandID,
                    ]
                );
                $pdo->commit();
        
            } catch (\Exception $e) {
                echo "Uh oh! {$e->getMessage()}";
                $pdo->rollBack();
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    $e->getMessage(),
                    "",
                    ""
                );
            }
    
        }
        else
        {
            try {
                Capsule::table('tblcustomfieldsvalues')
                    ->where('fieldid', $fieldid)
                    ->where('relid', $service_id)
                    ->update(
                        [
                            'value' => $commandID,
                        ]
                    );
            
            } catch (\Exception $e) {
                echo "I couldn't update client names. {$e->getMessage()}";
            }
        }

    } catch (Exception $e) {

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }


}
/**
 * Test connection with the given server parameters.
 *
 * Allows an admin user to verify that an API connection can be
 * successfully made with the given configuration parameters for a
 * server.
 *
 * When defined in a module, a Test Connection button will appear
 * alongside the Server Type dropdown when adding or editing an
 * existing server.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function kametera_TestConnection(array $params)
{
    try {

        // Call the service's connection test function.
        $clientId = $params['serverusername'];
        $secret = $params['serverpassword'];
        $serverhostname = $params['serverhostname'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://" . $serverhostname . "/service/authenticate");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'clientId'       => $clientId,
            'secret'         => $secret
        )));

        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status == 200)
        {
            $credentials = '{"clientId": "' . $clientId . '", "secret": "' . $secret . '"}';
            $fp = fopen(dirname(__FILE__) .'/storage/connection','w'); 
            fwrite($fp,$credentials);
            fclose($fp);
            
            try {
                $results1 = Capsule::table('information_schema.tables')
                    ->where('table_name', 'tblkamcommands')->first();
                $results2 = Capsule::table('information_schema.tables')
                    ->where('table_name', 'tblkamtasksqueue')->first();
                if (count($results1) > 0 && count($results2) > 0)
                {
                    $success = true;
                    $errorMsg = '';        
                }
                else
                {
                    if (count($results1) < 1)
                    {
                        try {
                            Capsule::schema()->create(
                                'tblkamcommands',
                                function ($table) {
                                    /** @var \Illuminate\Database\Schema\Blueprint $table */
                                    $table->increments('id');
                                    $table->string('service_id');
                                    $table->string('command_id');
                                    $table->string('status');
                                    $table->string('description');
                                    $table->integer('queueid')->nullable();
                                    $table->timestamps();
                                }
                            );
                        } catch (\Exception $e) {
                            $success = false;
                            $errorMsg = $e->getMessage();
                        }    
                    }
                    if (count($results2) < 1)
                    {
                        try {
                            Capsule::schema()->create(
                                'tblkamtasksqueue',
                                function ($table) {
                                    /** @var \Illuminate\Database\Schema\Blueprint $table */
                                    $table->increments('id');
                                    $table->string('operation');
                                    $table->string('server_id');
                                    $table->string('requested_value');
                                    $table->string('status');
                                    $table->timestamps();
                                }
                            );
                        } catch (\Exception $e) {
                            $success = false;
                            $errorMsg = $e->getMessage();
                        }    
                    }
                    $success = true;
                    $errorMsg = '';        
                }
            } catch (\Exception $e) {
                $success = false;
                $errorMsg = 'An error occured while connecting with database.';        
            }

        }
        else
        {
            $success = false;
            $errorMsg = 'Connection failed.';
        }
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        $success = false;
        $errorMsg = $e->getMessage();
    }

    return array(
        'success' => $success,
        'error' => $errorMsg,
    );
}

/**
 * Define product configuration options.
 *
 * The values you return here define the configuration options that are
 * presented to a user when configuring a product for use with the module. These
 * values are then made available in all module function calls with the key name
 * configoptionX - with X being the index number of the field from 1 to 24.
 *
 * You can specify up to 24 parameters, with field types:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each and their possible configuration parameters are provided in
 * this sample function.
 *
 * @see https://developers.whmcs.com/provisioning-modules/config-options/
 *
 * @return array
 */
function kametera_ConfigOptions()
{
    return array(
        //a text field type allows for single line text input
        // 'Text Field' => array(
        //     'Type' => 'text',
        //     'Size' => '25',
        //     'Default' => '1024',
        //     'Description' => 'Enter in megabytes',
        // ),
        // // a password field type allows for masked text input
        // 'Password Field' => array(
        //     'Type' => 'password',
        //     'Size' => '25',
        //     'Default' => '',
        //     'Description' => 'Enter secret value here',
        // ),
        // // the yesno field type displays a single checkbox option
        'Fetch Fresh Data' => array(
            'Type' => 'yesno',
            'Description' => 'Tick to update data for product configuration.',
        ),
        // the dropdown field type renders a select menu of options
        // 'Dropdown Field' => array(
        //     'Type' => 'dropdown',
        //     'Loader' => 'kametera_dataCentersLoader',
        //     // 'Options' => array(
        //     //     'option1' => 'Display Value 1',
        //     //     'option2' => 'Second Option',
        //     //     'option3' => 'Another Option',
        //     // ),
        //     'Description' => 'Choose one',
        // ),
        // //the radio field type displays a series of radio button options
        // 'Radio Field' => array(
        //     'Type' => 'radio',
        //     'Options' => 'First Option,Second Option,Third Option',
        //     'Description' => 'Choose your option!',
        // ),
        // // the textarea field type allows for multi-line text input
        // 'Textarea Field' => array(
        //     'Type' => 'textarea',
        //     'Rows' => '3',
        //     'Cols' => '60',
        //     'Description' => 'Freeform multi-line text input field',
        // ),
        // 'Button' => array(
        //     'Type' => 'button'
        // ),
    );
}
function kametera_listServers($clientId, $secret)
{
    try{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/servers");
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
            return json_decode($body);
        }
        else
        {
            logModuleCall(
                'kametera',
                __FUNCTION__,
                "An unknown error occured while fetching servers from Kametera.",
                "",
                ""
            );
        }
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}
/**
 * Provision a new instance of a product/service.
 *
 * Attempt to provision a new instance of a given product/service. This is
 * called any time provisioning is requested inside of WHMCS. Depending upon the
 * configuration, this can be any of:
 * * When a new order is placed
 * * When an invoice for a new order is paid
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function kametera_CreateAccount(array $params)
{
    try {
        
        // Call the service's provisioning function, using the values provided
        // by WHMCS in `$params`.
        //
        // A sample `$params` array may be defined as:
        //
        // ```
        // array(
        //     'domain' => 'The domain of the service to provision',
        //     'username' => 'The username to access the new service',
        //     'password' => 'The password to access the new service',
        //     'configoption1' => 'The amount of disk space to provision',
        //     'configoption2' => 'The new services secret key',
        //     'configoption3' => 'Whether or not to enable FTP',
        //     ...
        // )
        // ```
        // Call the service's connection test function.

        $clientId = $params['serverusername'];
        $secret = $params['serverpassword'];
        $serverhostname = $params['serverhostname'];
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            "",
            ""
        );
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params["model"]["productAddon"]["name"],
            $params["model"]["productAddon"]["module"],
            ""
        );
        
        if ($params["customfields"]["Command ID"] != "")
        {
            $creation_status = kametera_getCommandStatus($clientId, $secret, $params["customfields"]["Command ID"]);

            if ($creation_status == "pending")
            {
                return "Please hold on. Kametera is working to complete this order. If issue persists, contact support.";
            }
            elseif ($creation_status == "executing"){
                return "Kametera has already started creating server for this order.";
            }
            elseif ($creation_status == "progress"){
                return "A server creation for this order is already in progress.";
            }
            elseif ($creation_status == "complete"){
                return "There is already an associated server created on Kametera.";
            }
            elseif ($creation_status == "error"){
                return "Command ID box must be blank and then saved before performing this operation.";
            }
            elseif ($creation_status == "cancelled"){
                return "Server creation has been cancelled.";
            }
        }
        elseif($params["customfields"]["Server ID"] != "")
        {

            $datacenter = $params["configoptions"]["datacenters"];
            $name = "whmcs_service_id_" . $params["serviceid"];
            $password = $params["password"];
            $cpu = $params["configoptions"]["cpus"];
            $ram = preg_replace("/[^0-9]/", "", $params["configoptions"]["ram"] );
            $billing = "hourly";
            $managed = 0;
            $backup = $params["configoptions"]["backup"];
            $power = 1;
            $traffic = $params["configoptions"]["traffic"];
            $networkName0 = $params["configoptions"]["networks"];
            $diskSize0 = preg_replace("/[^0-9]/", "", $params["configoptions"]["disks"]);
            $diskSize1 = preg_replace("/[^0-9]/", "", $params["configoptions"]["disk1"]);
            $diskSize2 = preg_replace("/[^0-9]/", "", $params["configoptions"]["disk2"]);            

            $ch = curl_init();curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "AuthClientId: {$clientId}",
                "AuthSecret: {$secret}",
                "Content-Type: application/x-www-form-urlencoded"
            ));
            $data = array(
                'source'    => $params["customfields"]["Server ID"],
                'datacenter'       => $datacenter,
                'password'  => $password,
                'name'      => $name,
                'cpu'       => $cpu,
                'ram'       => $ram,
                'traffic'   => $traffic,
                'billing'   => $billing,
                'disk_size_0'      => $diskSize0,
                'backup'           => $backup,
            );
            if ($diskSize1 != 0 && $diskSize2 != 0){
                $data = array(
                    'source'    => $params["customfields"]["Server ID"],
                    'datacenter'=> $datacenter,
                    'password'  => $password,
                    'name'      => $name,
                    'cpu'       => $cpu,
                    'ram'       => $ram,
                    'traffic'   => $traffic,
                    'billing'   => $billing,
                    'disk_size_0'      => $diskSize0,
                    'disk_size_1'      => $diskSize1,
                    'disk_size_2'      => $diskSize2,
                    'backup'           => $backup,
                );
            }
            elseif($diskSize1 != 0 && $diskSize2 == 0){
                $data = array(
                    'source'    => $params["customfields"]["Server ID"],
                    'datacenter'=> $datacenter,
                    'password'  => $password,
                    'name'      => $name,
                    'cpu'       => $cpu,
                    'ram'       => $ram,
                    'traffic'   => $traffic,
                    'billing'   => $billing,
                    'disk_size_0'      => $diskSize0,
                    'disk_size_1'      => $diskSize1,
                    'backup'           => $backup,
                );
            }
            elseif($diskSize1 == 0 && $diskSize2 != 0){
                $data = array(
                    'source'    => $params["customfields"]["Server ID"],
                    'datacenter'=> $datacenter,
                    'password'  => $password,
                    'name'      => $name,
                    'cpu'       => $cpu,
                    'ram'       => $ram,
                    'traffic'   => $traffic,
                    'billing'   => $billing,
                    'disk_size_0'      => $diskSize0,
                    'disk_size_1'      => $diskSize2,
                    'backup'           => $backup,
                );
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            $body = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $commandID = json_decode($body);
            if ($status == 200)
            {
    
                $fieldid = kametera_createCommandIdField($params["pid"]);
                
                kametera_saveCommandIdField($fieldid, $params["serviceid"], $commandID[0]);
    
                $command = 'TriggerNotificationEvent';
                $postData = array(
                    'notification_identifier' => 'kametera.server.add',
                    'title' => 'You have successfully requested server provision.',
                    'message' => 'You have successfully requested server provision. It may take upto 10minutes. Please hold on while we are preparing your server.',
                    'url' => '/clientarea.php?action=productdetails&id='. $params["serviceid"],
                    'status' => 'Success',
                    'statusStyle' => 'info'
                );
    
                $results = localAPI($command, $postData);
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    $results,
                    "",
                    ""
                );
                return 'success';
            }
            else
            {
                $e = json_decode($body);
                return $e->errors[0]->info;
            }
        }
        else
        {

            $datacenter = $params["configoptions"]["datacenters"];
            $name = "whmcs_service_id_" . $params["serviceid"];
            $password = $params["password"];
            $cpu = $params["configoptions"]["cpus"];
            $ram = preg_replace("/[^0-9]/", "", $params["configoptions"]["ram"] );
            $billing = "hourly";
            $managed = 0;
            $backup = $params["configoptions"]["backup"];
            $power = 1;
            $traffic = $params["configoptions"]["traffic"];
            
            $diskSize0 = preg_replace("/[^0-9]/", "", $params["configoptions"]["disks"]);
            $diskSize1 = preg_replace("/[^0-9]/", "", $params["configoptions"]["disk1"]);
            $diskSize2 = preg_replace("/[^0-9]/", "", $params["configoptions"]["disk2"]);
            
            $diskSrc0 = $params["configoptions"]["images"];
            $networkName0 = $params["configoptions"]["networks"];
            
            //$networkName0 = $params["configoptions"]["networks"];
            
            // $networkName1 = "my-existing-lan";
            // $networkIp1 = "auto";
            
            // $networkName2 = "my-new-lan";
            // $networkSubnet2 = "172.16.0.0";
            // $networkBits2 = 24;
            // $networkIp2 = "auto";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "AuthClientId: {$clientId}",
                "AuthSecret: {$secret}",
                "Content-Type: application/x-www-form-urlencoded"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            logModuleCall(
                'kametera',
                __FUNCTION__,
                $diskSize1,
                "Disk1",
                ""
            );
            logModuleCall(
                'kametera',
                __FUNCTION__,
                $diskSize2,
                "Disk2",
                ""
            );

            if ($diskSize1 != 0 && $diskSize2 != 0)
            {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                    'datacenter'       => $datacenter,
                    'name'             => $name,
                    'password'         => $password,
                    'cpu'              => $cpu,
                    'ram'              => $ram,
                    'billing'          => $billing,
                    'managed'          => $managed,
                    'backup'           => $backup,
                    'power'            => $power,
                    'traffic'          => $traffic,
                    'disk_size_0'      => $diskSize0,
                    'disk_size_1'      => $diskSize1,
                    'disk_size_2'      => $diskSize2,
                    'disk_src_0'       => $diskSrc0,
                    'network_name_0'   => $networkName0,
                    // 'network_name_1'   => $networkName1,
                    // 'network_ip_1'     => $networkIp1,
                    // 'network_name_2'   => $networkName2,
                    // 'network_subnet_2' => $networkSubnet2,
                    // 'network_bits_2'   => $networkBits2,
                    // 'network_ip_2'     => $networkIp2
                )));    
            }
            elseif($diskSize1 != 0 && $diskSize2 == 0){
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                    'datacenter'       => $datacenter,
                    'name'             => $name,
                    'password'         => $password,
                    'cpu'              => $cpu,
                    'ram'              => $ram,
                    'billing'          => $billing,
                    'managed'          => $managed,
                    'backup'           => $backup,
                    'power'            => $power,
                    'traffic'          => $traffic,
                    'disk_size_0'      => $diskSize0,
                    'disk_src_0'       => $diskSrc0,
                    'disk_size_1'      => $diskSize1,                    
                    'network_name_0'   => $networkName0,
                    // 'network_name_1'   => $networkName1,
                    // 'network_ip_1'     => $networkIp1,
                    // 'network_name_2'   => $networkName2,
                    // 'network_subnet_2' => $networkSubnet2,
                    // 'network_bits_2'   => $networkBits2,
                    // 'network_ip_2'     => $networkIp2
                ))); 
            }
            elseif($diskSize1 == 0 && $diskSize2 != 0){
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                    'datacenter'       => $datacenter,
                    'name'             => $name,
                    'password'         => $password,
                    'cpu'              => $cpu,
                    'ram'              => $ram,
                    'billing'          => $billing,
                    'managed'          => $managed,
                    'backup'           => $backup,
                    'power'            => $power,
                    'traffic'          => $traffic,
                    'disk_size_0'      => $diskSize0,
                    'disk_size_1'      => $diskSize2,                    
                    'disk_src_0'       => $diskSrc0,
                    'network_name_0'   => $networkName0,
                    // 'network_name_1'   => $networkName1,
                    // 'network_ip_1'     => $networkIp1,
                    // 'network_name_2'   => $networkName2,
                    // 'network_subnet_2' => $networkSubnet2,
                    // 'network_bits_2'   => $networkBits2,
                    // 'network_ip_2'     => $networkIp2
                ))); 
            }
            else
            {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                    'datacenter'       => $datacenter,
                    'name'             => $name,
                    'password'         => $password,
                    'cpu'              => $cpu,
                    'ram'              => $ram,
                    'billing'          => $billing,
                    'managed'          => $managed,
                    'backup'           => $backup,
                    'power'            => $power,
                    'traffic'          => $traffic,
                    'disk_size_0'      => $diskSize0,
                    'disk_src_0'       => $diskSrc0,
                    'network_name_0'   => $networkName0,
                    // 'network_name_1'   => $networkName1,
                    // 'network_ip_1'     => $networkIp1,
                    // 'network_name_2'   => $networkName2,
                    // 'network_subnet_2' => $networkSubnet2,
                    // 'network_bits_2'   => $networkBits2,
                    // 'network_ip_2'     => $networkIp2
                )));                
            }            
            $body = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            logModuleCall(
                'kametera',
                __FUNCTION__,
                $body,
                "Body",
                ""
            );
            $commandID = json_decode($body)->CmdIds[0];
    
            if ($status == 200)
            {
    
                $fieldid = kametera_createCommandIdField($params["pid"]);
                
                kametera_saveCommandIdField($fieldid, $params["serviceid"], $commandID[0]);
    
                $command = 'TriggerNotificationEvent';
                $postData = array(
                    'notification_identifier' => 'kametera.server.add',
                    'title' => 'You have successfully requested server provision.',
                    'message' => 'You have successfully requested server provision. It may take upto 10minutes. Please hold on while we are preparing your server.',
                    'url' => '/clientarea.php?action=productdetails&id='. $params["serviceid"],
                    'status' => 'Success',
                    'statusStyle' => 'info'
                );
    
                $results = localAPI($command, $postData);
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    $results,
                    "",
                    ""
                );
                return 'success';
            }
            else
            {
                $e = json_decode($body);
                return $e->errors[0]->info;
            }
    
        }

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}

/**
 * Suspend an instance of a product/service.
 *
 * Called when a suspension is requested. This is invoked automatically by WHMCS
 * when a product becomes overdue on payment or can be called manually by admin
 * user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function kametera_SuspendAccount(array $params)
{
    try {
        // Call the service's suspend function, using the values provided by
        // WHMCS in `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Un-suspend instance of a product/service.
 *
 * Called when an un-suspension is requested. This is invoked
 * automatically upon payment of an overdue invoice for a product, or
 * can be called manually by admin user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function kametera_UnsuspendAccount(array $params)
{
    try {
        // Call the service's unsuspend function, using the values provided by
        // WHMCS in `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Terminate instance of a product/service.
 *
 * Called when a termination is requested. This can be invoked automatically for
 * overdue products if enabled, or requested manually by an admin user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function kametera_TerminateAccount(array $params)
{
    try {
        // Call the service's terminate function, using the values provided by
        // WHMCS in `$params`.

        $clientId = $params['serverusername'];
        $secret = $params['serverpassword'];

        $serverId = "";
        if (isset($params["model"]["subscriptionid"]))
        {
            $serverId = $params["model"]["subscriptionid"];
        }
        $confirm = 1;
        $force = 1;

        if ($serverId != "")
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$serverId}/terminate");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "AuthClientId: {$clientId}",
                "AuthSecret: {$secret}",
                "Content-Type: application/x-www-form-urlencoded"
            ));
            curl_setopt($ch,
            CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                'confirm'   => $confirm,
                'force'   => $force
            )));
            $body = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
    
            if ($status == 200)
            {
                kametera_updateSubscriptionID($params["serviceid"], "");
                $fieldid = kametera_createCommandIdField($params["pid"]);
                kametera_saveCommandIdField($fieldid, $params["serviceid"], "");
                
                return 'success';
            }
            else
            {
                $e = json_decode($body);
                return $e->errors[0]->info;
            }
    
        }
        else
        {
            return "No subscription exists in Kametera.";
        }

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}

/**
 * Change the password for an instance of a product/service.
 *
 * Called when a password change is requested. This can occur either due to a
 * client requesting it via the client area or an admin requesting it from the
 * admin side.
 *
 * This option is only available to client end users when the product is in an
 * active status.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function kametera_ChangePassword(array $params)
{
    try {

        $e = "";
        $clientId = $params['serverusername'];
        $secret = $params['serverpassword'];

        $serverId = "";
        if (isset($params["model"]["subscriptionid"]))
        {
            $serverId = $params["model"]["subscriptionid"];
        }
        
        $password = $params["password"];
        
        if ($serverId != "")
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,
            "https://console.kamatera.com/service/server/{$serverId}/password");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "AuthClientId: {$clientId}",
                "AuthSecret: {$secret}",
                "Content-Type: application/x-www-form-urlencoded"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                'password' => $password
            )));
            $body = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
    
            if ($status == 200)
            {
                $command = 'SendEmail';
                $postData = array(
                    'id' => $params["serviceid"],
                    'customtype' => 'product',
                    'customsubject' => 'Your server password has been changed.',
                    'custommessage' => '<p>Thank you for choosing us.</p><p>Your new password is below:</p><p>{$password}</p>',
                    'customvars' => base64_encode(serialize(array("password"=>$password))),
                );

                $results = localAPI($command, $postData);

                return 'success';
            }
            else
            {
                $e = json_decode($body);
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    $e->errors[0]->info,
                    "",
                    ""
                );
                return $e->errors[0]->info;
            }
        }
        else
        {
            return 'error';
        }

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

}
/**
 * Modify Server CPU Configurations
 */
function kametera_changeCPU($clientId, $secret, $serverId, $cpu)
{
    try{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$serverId}/cpu");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "AuthClientId: {$clientId}",
            "AuthSecret: {$secret}",
            "Content-Type: application/x-www-form-urlencoded"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'cpu'   => $cpu
        )));
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = [];
        if ($status == 200)
        {
            logModuleCall(
                'kametera',
                __FUNCTION__,
                json_decode($body),
                "",
                ""
            );
    
            $data["code"] = json_decode($body);
            $data["message"] = "success";
            return $data;
        }
        else
        {
            $data["code"] = json_decode($body)->errors[0]->code;
            $data["message"] = json_decode($body)->errors[0]->info;
            return $data;
        }
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}
/**
 * Modify Server RAM Configurations
 */
function kametera_changeRAM($clientId, $secret, $serverId, $ram)
{
    try{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$serverId}/ram");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "AuthClientId: {$clientId}",
            "AuthSecret: {$secret}",
            "Content-Type: application/x-www-form-urlencoded"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'ram'   => $ram
        )));
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = [];
        if ($status == 200)
        {
            logModuleCall(
                'kametera',
                __FUNCTION__,
                json_decode($body),
                "",
                ""
            );
    
            $data["code"] = json_decode($body);
            $data["message"] = "success";
            return $data;
        }
        else
        {
            $data["code"] = json_decode($body)->errors[0]->code;
            $data["message"] = json_decode($body)->errors[0]->info;
            return $data;
        }
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}
/**
 * Resize Disk
 */
function kametera_resizeHardDisk($clientId, $secret, $serverId, $size, $index, $provision)
{
    try{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$serverId}/disk");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "AuthClientId: {$clientId}",
            "AuthSecret: {$secret}",
            "Content-Type: application/x-www-form-urlencoded"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'size'      => $size,
            'index'     => $index,
            'provision' => $provision
        )));
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $index,
            $body,
            ""
        );
        $data = [];
        if ($status == 200)
        {
            $data["code"] = json_decode($body);
            $data["message"] = "success";
            return $data;
        }
        else
        {
            $data["code"] = json_decode($body)->errors[0]->code;
            $data["message"] = json_decode($body)->errors[0]->info;
            return $data;
        }
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}
/**
 * Add New Hard Disk
 */
function kametera_addNewDisk($clientId, $secret, $serverId, $size)
{
    try{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,
        "https://console.kamatera.com/service/server/{$serverId}/disk");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "AuthClientId: {$clientId}",
            "AuthSecret: {$secret}",
            "Content-Type: application/x-www-form-urlencoded"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'size'   => $size
        )));
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = [];
        if ($status == 200)
        {
            $data["code"] = json_decode($body);
            $data["message"] = "success";
            return $data;
        }
        else
        {
            $data["code"] = json_decode($body)->errors[0]->code;
            $data["message"] = json_decode($body)->errors[0]->info;
            return $data;
        }
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}
/**
 * Remove Disk
 */
function kametera_removeDisk($clientId, $secret, $serverId, $index)
{
    $confirm = 1;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 
    "https://console.kamatera.com/service/server/{$serverId}/disk/remove");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "AuthClientId: {$clientId}",
        "AuthSecret: {$secret}",
        "Content-Type: application/x-www-form-urlencoded"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
        'index'     => $index,
        'confirm'   => $confirm
    )));
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = [];
    if ($status == 200)
    {
        $data["code"] = json_decode($body);
        $data["message"] = "success";
        return $data;
    }
    else
    {
        $data["code"] = json_decode($body)->errors[0]->code;
        $data["message"] = json_decode($body)->errors[0]->info;
        return $data;
    }
}
function kametera_snapshotExists($clientId, $secret, $serverId){

    try {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$serverId}/snapshots");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "AuthClientId: {$clientId}",
            "AuthSecret: {$secret}"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $results = json_decode($body);

        if ($status == 200)
        {
            if (count($results) > 0)
            {
                return true;
            }
            else
            {
                false;
            }
        }
        else
        {   
            logModuleCall(
                'kametera',
                __FUNCTION__,
                json_decode($body)->errors[0]->info,
                "",
                ""
            );
            return false;
        }
    }
    catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}
function kametera_removeAllSnapshots($clientId, $secret, $serverId){

    try {

    }
    catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $e->getMessage(),
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}
function kametera_ListSnapshots($clientId, $secret, $serverId)
{

    try {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$serverId}/snapshots");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "AuthClientId: {$clientId}",
            "AuthSecret: {$secret}"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = [];
        
        if ($status == 200)
        {
            $data["body"] = json_decode($body);
            $data["code"] = 1;
            $data["message"] = "success";
        }
        else
        {
            $data["body"] = [];
            $data["code"] = json_decode($body)->errors[0]->code;
            $data["message"] = json_decode($body)->errors[0]->info;
        }
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $serverId,
            $data,
            ""
        );

        return $data;
    }
    catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $e->getMessage(),
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}
/**
 * Upgrade or downgrade an instance of a product/service.
 *
 * Called to apply any change in product assignment or parameters. It
 * is called to provision upgrade or downgrade orders, as well as being
 * able to be invoked manually by an admin user.
 *
 * This same function is called for upgrades and downgrades of both
 * products and configurable options.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function kametera_ChangePackage(array $params)
{
    try {

        $clientId = $params['serverusername'];
        $secret = $params['serverpassword'];

        $serverId = "";
        if (isset($params["model"]["subscriptionid"]))
        {
            $serverId = $params["model"]["subscriptionid"];
            $serverInfo = kametera_getServerInformation($clientId, $secret, $serverId);

            kametera_deleteAllSS($clientId, $secret, $serverId);
            
            //this part configures CPU
            if ($serverInfo->cpu != $params["configoptions"]["cpus"])
            {
                
                $commandId = kametera_changeCPU($clientId, $secret, $serverId, $params["configoptions"]["cpus"]);

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
                            'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                        );
                    
                        $statement->execute(
                            [
                                ':service_id' => $serverId,
                                ':command_id' => $commandId["code"],
                                ':status' => $status,
                                ':description' => "cpu",
                            ]
                        );
                    
                        $pdo->commit();

                        return $commandId["message"];
                    } catch (\Exception $e) {
                        logModuleCall(
                            'kametera',
                            __FUNCTION__,
                            "",
                            $e->getMessage(),
                            $e->getTraceAsString()
                        );
                        $pdo->rollBack();
                    }
            }
            
            //this part configures RAM
            else if ($serverInfo->ram != preg_replace("/[^0-9]/", "", $params["configoptions"]["ram"]))
            {
                
                $commandId = kametera_changeRAM($clientId, $secret, $serverId, preg_replace("/[^0-9]/", "", $params["configoptions"]["ram"]));
 
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
                            'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                        );
                    
                        $statement->execute(
                            [
                                ':service_id' => $serverId,
                                ':command_id' => $commandId["code"],
                                ':status' => $status,
                                ':description' => "ram",
                            ]
                        );
                    
                        $pdo->commit();
                    } catch (\Exception $e) {
                        logModuleCall(
                            'kametera',
                            __FUNCTION__,
                            "",
                            $e->getMessage(),
                            $e->getTraceAsString()
                        );
                        $pdo->rollBack();
                    }
            }                  
            //this part resize System Disk
            else if ($serverInfo->diskSizes[0] != preg_replace("/[^0-9]/", "", $params["configoptions"]["disks"]))
            {
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    "",
                    "",
                    ""
                );
                $commandId = kametera_resizeHardDisk($clientId, $secret, $serverId, preg_replace("/[^0-9]/", "", $params["configoptions"]["disks"]), 0, 1);
 
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
                        'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                    );
                
                    $statement->execute(
                        [
                            ':service_id' => $serverId,
                            ':command_id' => $commandId["code"],
                            ':status' => $status,
                            ':description' => "disk0",
                        ]
                    );
                
                    $pdo->commit();
                } catch (\Exception $e) {
                    logModuleCall(
                        'kametera',
                        __FUNCTION__,
                        "",
                        $e->getMessage(),
                        $e->getTraceAsString()
                    );
                    $pdo->rollBack();
                }
            }
            //remove disk 1
            else if (count($serverInfo->diskSizes) >= 2 && preg_replace("/[^0-9]/", "", $params["configoptions"]["disk1"]) == 0)
            {
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    "",
                    "",
                    ""
                );
                $commandId = kametera_removeDisk($clientId, $secret, $serverId, 1);

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
                        'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                    );
                
                    $statement->execute(
                        [
                            ':service_id' => $serverId,
                            ':command_id' => $commandId["code"],
                            ':status' => $status,
                            ':description' => "disk1-remove",
                        ]
                    );
                
                    $pdo->commit();
                } catch (\Exception $e) {
                    logModuleCall(
                        'kametera',
                        __FUNCTION__,
                        "",
                        $e->getMessage(),
                        $e->getTraceAsString()
                    );
                    $pdo->rollBack();
                }               

            }
            //remove disk 2
            else if (count($serverInfo->diskSizes) >= 2 && preg_replace("/[^0-9]/", "", $params["configoptions"]["disk2"]) == 0)
            {
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    "",
                    "",
                    ""
                );
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    count($serverInfo->diskSizes),
                    "Disk Sizes",
                   ""
                );
                if (count($serverInfo->diskSizes) == 3)
                {
                    $commandId = kametera_removeDisk($clientId, $secret, $serverId, 2);
                }
                else if (count($serverInfo->diskSizes) == 2)
                {
                    $commandId = kametera_removeDisk($clientId, $secret, $serverId, 1);
                }
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    $commandId,
                    "Command ID",
                   ""
                );
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
                        'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                    );
                
                    $statement->execute(
                        [
                            ':service_id' => $serverId,
                            ':command_id' => $commandId["code"],
                            ':status' => $status,
                            ':description' => "disk2-remove",
                        ]
                    );
                
                    $pdo->commit();
                } catch (\Exception $e) {
                    logModuleCall(
                        'kametera',
                        __FUNCTION__,
                        "",
                        $e->getMessage(),
                        $e->getTraceAsString()
                    );
                    $pdo->rollBack();
                }               
            }
            else if (count($serverInfo->diskSizes) == 1 && preg_replace("/[^0-9]/", "", $params["configoptions"]["disk1"]) != 0)
            {
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    "",
                    "",
                    ""
                );
                $commandId = kametera_addNewDisk($clientId, $secret, $serverId, preg_replace("/[^0-9]/", "", $params["configoptions"]["disk1"]));

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
                        'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                    );
                
                    $statement->execute(
                        [
                            ':service_id' => $serverId,
                            ':command_id' => $commandId["code"],
                            ':status' => $status,
                            ':description' => "disk1-create",
                        ]
                    );
                
                    $pdo->commit();
                } catch (\Exception $e) {
                    logModuleCall(
                        'kametera',
                        __FUNCTION__,
                        "",
                        $e->getMessage(),
                        $e->getTraceAsString()
                    );
                    $pdo->rollBack();
                }               
            }
            else if (count($serverInfo->diskSizes) == 2 && preg_replace("/[^0-9]/", "", $params["configoptions"]["disk2"]) != 0)
            {
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    "",
                    "",
                    ""
                );
                $commandId = kametera_addNewDisk($clientId, $secret, $serverId, preg_replace("/[^0-9]/", "", $params["configoptions"]["disk2"]));
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
                        'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                    );
                
                    $statement->execute(
                        [
                            ':service_id' => $serverId,
                            ':command_id' => $commandId["code"],
                            ':status' => $status,
                            ':description' => "disk2-create",
                        ]
                    );
                
                    $pdo->commit();
                } catch (\Exception $e) {
                    logModuleCall(
                        'kametera',
                        __FUNCTION__,
                        "",
                        $e->getMessage(),
                        $e->getTraceAsString()
                    );
                    $pdo->rollBack();
                }               

            }
            else if ($serverInfo->diskSizes[1] != preg_replace("/[^0-9]/", "", $params["configoptions"]["disk1"]) && preg_replace("/[^0-9]/", "", $params["configoptions"]["disk1"]) != 0)
            {
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    "",
                    "",
                    ""
                );
                $commandId = kametera_resizeHardDisk($clientId, $secret, $serverId, preg_replace("/[^0-9]/", "", $params["configoptions"]["disk1"]), 1, 1);
                // $command_status = kametera_getCommandStatus($clientId, $secret, $commandId);
                // while ($command_status != "complete")
                // {
                //     $command_status = kametera_getCommandStatus($clientId, $secret, $commandId);
                //     echo "Disk Resizing -> " . $command_status;
                // }
                // $commandId = "";            
                // $command_status = "";     

                        // $command_status = "";

                        // if (!is_numeric($commandId))
                        // {
                        //     $command_status = $commandId;
                        // }
                        // else
                        // {
                        //     $command_status = kametera_getCommandStatus($clientId, $secret, $commandId);
                        // }
                // while ($command_status != "complete")
                // {
                //     $command_status = kametera_getCommandStatus($clientId, $secret, $commandId);
                //     echo "Configuring CPU -> " . $command_status;
                // }
                // $commandId = "";            
                // $command_status = "";  
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
                        'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                    );
                
                    $statement->execute(
                        [
                            ':service_id' => $serverId,
                            ':command_id' => $commandId["code"],
                            ':status' => $status,
                            ':description' => "disk1",
                        ]
                    );
                
                    $pdo->commit();
                } catch (\Exception $e) {
                    logModuleCall(
                        'kametera',
                        __FUNCTION__,
                        "",
                        $e->getMessage(),
                        $e->getTraceAsString()
                    );
                    $pdo->rollBack();
                }
            }            
            else if ($serverInfo->diskSizes[2] != preg_replace("/[^0-9]/", "", $params["configoptions"]["disk2"]) && preg_replace("/[^0-9]/", "", $params["configoptions"]["disk2"]) != 0)
            {
                logModuleCall(
                    'kametera',
                    __FUNCTION__,
                    "",
                    "",
                    ""
                );
                $commandId = kametera_resizeHardDisk($clientId, $secret, $serverId, preg_replace("/[^0-9]/", "", $params["configoptions"]["disk2"]), 2, 1);
                // $command_status = kametera_getCommandStatus($clientId, $secret, $commandId);
                // while ($command_status != "complete")
                // {
                //     $command_status = kametera_getCommandStatus($clientId, $secret, $commandId);
                //     echo "Disk Resizing -> " . $command_status;
                // }
                // $commandId = "";            
                // $command_status = "";     

                        // $command_status = "";

                        // if (!is_numeric($commandId))
                        // {
                        //     $command_status = $commandId;
                        // }
                        // else
                        // {
                        //     $command_status = kametera_getCommandStatus($clientId, $secret, $commandId);
                        // }
                // while ($command_status != "complete")
                // {
                //     $command_status = kametera_getCommandStatus($clientId, $secret, $commandId);
                //     echo "Configuring CPU -> " . $command_status;
                // }
                // $commandId = "";            
                // $command_status = "";  
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
                        'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                    );
                
                    $statement->execute(
                        [
                            ':service_id' => $serverId,
                            ':command_id' => $commandId["code"],
                            ':status' => $status,
                            ':description' => "disk2",
                        ]
                    );
                
                    $pdo->commit();
                } catch (\Exception $e) {
                    logModuleCall(
                        'kametera',
                        __FUNCTION__,
                        "",
                        $e->getMessage(),
                        $e->getTraceAsString()
                    );
                    $pdo->rollBack();
                }
            }
            return 'success';            
        }
        else
        {
            return "You are not subscribed to this service.";
        }

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}

/**
 * Additional actions an admin user can invoke.
 *
 * Define additional actions that an admin user can perform for an
 * instance of a product/service.
 *
 * @see kametera_buttonOneFunction()
 *
 * @return array
 */
function kametera_AdminCustomButtonArray()
{
    return array(
        "Refresh to Sync with Kametera" => "Refresh",
        "Check Server Provision Status" => "ProvisionStatus"

    );
}
/**
 * Check if subscription has been assigned or not
 */
function kametera_isAddonSubscribed($service_id, $subscription_id)
{
    if (isset($subscription_id) && isset($service_id))
    {
        $addons = Capsule::table('tblhosting')
        ->join('tblhostingaddons', 'tblhosting.id', '=' , 'tblhostingaddons.hostingid')
        ->where('tblhostingaddons.hostingid', $service_id)
        ->where('tblhostingaddons.status', 'Active')->get();
        
        if (count($addons) > 0)
        {
            foreach ($addons as $data)
            {
                if ($data->subscriptionid == "")
                {
                    try {
                        Capsule::table('tblhostingaddons')
                            ->where('hostingid', $service_id)
                            ->where('subscriptionid', '')
                            ->update(
                                [
                                    'subscriptionid' => $subscription_id,
                                ]
                            );
                    
                    } catch (\Exception $e) {
                        logModuleCall(
                            'kametera',
                            __FUNCTION__,
                            "",
                            $e->getMessage(),
                            $e->getTraceAsString()
                        );
                    }    
                }
            }
            return true;
        }
    }
    return false;
}
/**
 * Additional actions a client user can invoke.
 *
 * Define additional actions a client user can perform for an instance of a
 * product/service.
 *
 * Any actions you define here will be automatically displayed in the available
 * list of actions within the client area.
 *
 * @return array
 */
function kametera_ClientAreaCustomButtonArray(array $params)
{
    if (kametera_subscriptionIDVerification($params["serviceid"]) == 0)
    {   
        return array(
            "Start" => "startServer",
            "Restart" => "restartServer",
            "Shutdown" => "shutdownServer",
            "Clone" => "cloneServer",
            "Snapshots" => "snapshotsList",
            "Configure CPU" => "configureCPU",        
            "Configure RAM" => "configureRAM",    
            "Configure Primary Disk" => "configurePrimaryDisk",
            "Configure S. Disk A" => "configureSecondaryDiskA",
            "Configure S. Disk B" => "configureSecondaryDiskB",
            // "Upgrade/Downgrade Snapshots" => "confSnapshots",
            // "Backup Subscription" => "backup",
            "SS Create" => "ssCreate",
            "SS One Delete" => "ssOneDelete",
            "SS Two Delete" => "ssTwoDelete",
            "SS Three Delete" => "ssThreeDelete",
            "SS Four Delete" => "ssFourDelete",
            "SS One Revert" => "ssOneRevert",
            "SS Two Revert" => "ssTwoRevert",
            "SS Three Revert" => "ssThreeRevert",
            "SS Four Revert" => "ssFourRevert",

            //"Terminate" => "terminateServer"
        );   
        // if (kametera_isAddonSubscribed($params["serviceid"], $params["model"]["subscriptionid"]))
        // {
     
        // }
        // else
        // {
        //     return array(
        //         "Start" => "startServer",
        //         "Restart" => "restartServer",
        //         "Shutdown" => "shutdownServer",
        //         "Clone" => "cloneServer",
        //         // "Create Snapshot" => "createSnapshot",
        //         "Configure CPU" => "configureCPU",        
        //         "Configure RAM" => "configureRAM",    
        //         "Configure Primary Disk" => "configurePrimaryDisk",
        //         "Configure S. Disk A" => "configureSecondaryDiskA",
        //         "Configure S. Disk B" => "configureSecondaryDiskB",
        //         //"Terminate" => "terminateServer"
        //     );    
        // }
    }
}
/**
 * returns snapshots list page
 */
function kametera_snapshotsList(array $params)
{
    $clientId = $params['serverusername'];
    $secret = $params['serverpassword'];

    $serverId = "";
    if (isset($params["model"]["subscriptionid"]))
    {
        $serverId = $params["model"]["subscriptionid"];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$serverId}/snapshots");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "AuthClientId: {$clientId}",
        "AuthSecret: {$secret}"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $result = json_decode($body);

    if ($status == 200)
    {
        return array(
            'templatefile' => 'snapshotslist',
            'breadcrumb' => array('clientarea.php?action=productdetails&id=' . $params["serviceid"] . '&modop=custom&a=snapshotsList' => 'Snapshots'),
            'vars' => array(
                'results' => $result,
                'url' => 'clientarea.php?action=productdetails',
                'modop' => 'custom',
                'action' => 'ssCreate',
                'serviceid' => $params["serviceid"],
                'successful' => true,
                'errormessage' => false
            )
        );
    }
    else
    {
        return array(
            'templatefile' => 'snapshotslist',
            'breadcrumb' => array('clientarea.php?action=productdetails&id=' . $params["serviceid"] . '&modop=custom&a=snapshotsList' => 'Snapshots'),
            'error' => $result->errors[0]->info,
            'errormessage' => true,
            'successful' => false
        );
    }
}
function kametera_alreadyCreatedSnapshots($clientId, $secret, $serverId)
{
    $data_arr = kametera_ListSnapshots($clientId, $secret, $serverId);

    if ($data_arr["message"] == "success")
    {
        $ssList = $data_arr["body"];

        $i = 0;
        if (count($ssList) > 0)
        {
            $i = 1;
            $root = $ssList[0];
            logModuleCall(
                'kametera',
                __FUNCTION__,
                count($root->child),
                "",
                ""
            );
            while(count($root->child) > 0)
            {
                $root = $root->child[0];
                $i++;
            }        
        }
        return $i;
    }
    else
    {
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $data_arr["message"],
            "",
            ""
        );
        return -1;
    }
}
function kametera_createSnapshot($clientId, $secret, $serverId, $service_id){
        $data = [];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,
            "https://console.kamatera.com/service/server/{$serverId}/snapshot");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "AuthClientId: {$clientId}",
                "AuthSecret: {$secret}",
                "Content-Type: application/x-www-form-urlencoded"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                'name' => "whmcs_service_id_". $service_id
            )));
            $body = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);    

            if ($status == 200)
            {
                $data["code"] = json_decode($body);
                $data["message"] = "success";
            }
            else
            {
                $data["code"] = json_decode($body)->errors[0]->code;
                $data["message"] = json_decode($body)->errors[0]->info;
            }    
        
        return $data;
}

function kametera_ssCreate(array $params){
    $clientId = $params['serverusername'];
    $secret = $params['serverpassword'];

    $serverId = "";
    if (isset($params["model"]["subscriptionid"]))
    {
        $serverId = $params["model"]["subscriptionid"];
    }

    $number_of_existing_ss = kametera_alreadyCreatedSnapshots($clientId, $secret, $serverId);
    logModuleCall(
        'kametera',
        __FUNCTION__,
        "number_of_existing_ss: " . $number_of_existing_ss,
        $params["configoptions"]["snapshots"],
        ""
    );

    if ($params["configoptions"]["snapshots"] == 0)
    {
        return " - You haven't purchased snapshot.";
    }
    else if ($number_of_existing_ss == -1)
    {
        return " - An unknown error occured in API.";
    }
    else if ($number_of_existing_ss >= 4)
    {
        return "- Already created maximum number of screenshots. To create new, delete existing first.";
    }
    else if ((int)$params["configoptions"]["snapshots"] > $number_of_existing_ss)
    {
        if ($number_of_existing_ss >= 0 && $number_of_existing_ss < 4 )
        {
            $command = kametera_createSnapshot($clientId, $secret, $serverId, $params["serviceid"]);
    
            if (isset($command)){
                if ($command["message"] == "success"){
    
                    $data = Capsule::table('tblkamcommands')
                    ->where('service_id',  $params["model"]["subscriptionid"])
                    ->where('status', 'pendingx')->first();
                    
                    $pdo = Capsule::connection()->getPdo();
                    $pdo->beginTransaction();
    
                    if (count($data) > 0)
                    {
                        try {
                            $statement = $pdo->prepare(
                                'insert into tblkamtasksqueue (operation, requested_value, server_id, status) values (:operation, :requested_value, :server_id, :status)'
                            );
                        
                            $statement->execute(
                                [
                                    ':operation' => "create-ss",
                                    ':requested_value' => $params["model"]["clientId"] . "-ss-" . $params["configoptions"]["snapshots"],
                                    ':server_id' => $params["model"]["subscriptionid"],
                                    ':status' => "pending",
                                ]
                            );
                        
                            $pdo->commit();
                            return "success";
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
                    }
                    else
                    {
    
                
                        try {
                            $statement = $pdo->prepare(
                                'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                            );
                        
                            $statement->execute(
                                [
                                    ':service_id' => $serverId,
                                    ':command_id' => $command["code"],
                                    ':status' => "pendingx",
                                    ':description' => $params["model"]["clientId"] . "-ss",
                                ]
                            );
                        
                            $pdo->commit();
                            return "success";
                        } catch (\Exception $e) {
                            logModuleCall(
                                'kametera',
                                __FUNCTION__,
                                "",
                                $e->getMessage(),
                                $e->getTraceAsString()
                            );
                            $pdo->rollBack();
                        }        
        
                    }
                }
                else
                {
                    return "An unknown error occured while creating Snapshot. Contact Support.";
                }
            }
            else
            {
                return "An unknown error occured while creating Snapshot. Contact Support.";
            }
        }
    }
    return "An unknown error occured while creating Snapshot. Contact Support.";

}
function kametera_getSnapshotID($clientId, $secret, $serverId, $index)
{
    $data_arr = kametera_ListSnapshots($clientId, $secret, $serverId);

    if ($data_arr["message"] == "success")
    {
        $ssList = $data_arr["body"];

        if ($index == 1)
        {
            return $ssList[0]->id;
        }
        else if ($index == 2)
        {
            return $ssList[0]->child[0]->id;            
        }
        else if ($index == 3)
        {
            return $ssList[0]->child[0]->child[0]->id;            
        }
        else if ($index == 4)
        {
            return $ssList[0]->child[0]->child[0]->child[0]->id;            
        }
    }
    else
    {
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $data_arr["message"],
            "",
            ""
        );
        return -1;
    }

}
function kametera_deleteSnapshot($clientId, $secret, $serverId, $snapshotId){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$serverId}/snapshot");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "AuthClientId: {$clientId}",
        "AuthSecret: {$secret}",
        "Content-Type: application/x-www-form-urlencoded"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
        'snapshotId' => $snapshotId
    )));
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = [];
    if ($status == 200)
    {
        $data["code"] = json_decode($body);
        $data["message"] = "success";
    }
    else
    {
        $data["code"] = json_decode($body)->errors[0]->code;
        $data["message"] = json_decode($body)->errors[0]->info;
    } 
    return $data;
}
function kametera_revertSnapshot($clientId, $secret, $serverId, $snapshotId){

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,
    "https://console.kamatera.com/service/server/{$serverId}/snapshot");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "AuthClientId: {$clientId}",
        "AuthSecret: {$secret}",
        "Content-Type: application/x-www-form-urlencoded"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
        'snapshotId' => $snapshotId
    )));
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = [];
    if ($status == 200)
    {
        $data["code"] = json_decode($body)->cmdId;
        $data["message"] = "success";
    }
    else
    {
        $data["code"] = json_decode($body)->errors[0]->code;
        $data["message"] = json_decode($body)->errors[0]->info;
    } 
    return $data;
}

function kametera_deleteAllSS($clientId, $secret, $serverId){

    $number_of_existing_ss = kametera_alreadyCreatedSnapshots($clientId, $secret, $serverId);

    for ($i = $number_of_existing_ss ; $i >= 1 ; $i--)
    {
        $snapshotId = kametera_getSnapshotID($clientId, $secret, $serverId, $i);
        $command = kametera_deleteSnapshot($clientId, $secret, $serverId, $snapshotId);
        $command_status = kametera_getCommandStatus($clientId, $secret, $command["code"]);

        while ($command_status != "complete")
        {
            $command_status = kametera_getCommandStatus($clientId, $secret, $command["code"]);
        }
    }
}
function kametera_ssOneDelete(array $params){
    $clientId = $params['serverusername'];
    $secret = $params['serverpassword'];

    $serverId = "";
    if (isset($params["model"]["subscriptionid"]))
    {
        $serverId = $params["model"]["subscriptionid"];
    }

    $number_of_existing_ss = kametera_alreadyCreatedSnapshots($clientId, $secret, $serverId);

    if ($params["configoptions"]["snapshots"] == 0)
    {
        return " - You haven't purchased snapshot.";
    }
    else if ($number_of_existing_ss == -1)
    {
        return " - An unknown error occured in API.";
    }
    else if ($number_of_existing_ss > 0 && $number_of_existing_ss < 5)
    {
        $snapshotId = kametera_getSnapshotID($clientId, $secret, $serverId, 1);
        $command = kametera_deleteSnapshot($clientId, $secret, $serverId, $snapshotId);

        if (isset($command)){
            if ($command["message"] == "success"){

                $data = Capsule::table('tblkamcommands')
                ->where('service_id',  $params["model"]["subscriptionid"])
                ->where('status', 'pendingx')->first();
                
                $pdo = Capsule::connection()->getPdo();
                $pdo->beginTransaction();

                if (count($data) > 0)
                {
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamtasksqueue (operation, requested_value, server_id, status) values (:operation, :requested_value, :server_id, :status)'
                        );
                    
                        $statement->execute(
                            [
                                ':operation' => "delete-ss",
                                ':requested_value' => $params["model"]["clientId"] . "-ss",
                                ':server_id' => $params["model"]["subscriptionid"],
                                ':status' => "pending",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
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
                }
                else
                {

            
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                        );
                    
                        $statement->execute(
                            [
                                ':service_id' => $serverId,
                                ':command_id' => $command["code"],
                                ':status' => "pendingx",
                                ':description' => $params["model"]["clientId"] . "-ss",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
                    } catch (\Exception $e) {
                        logModuleCall(
                            'kametera',
                            __FUNCTION__,
                            "",
                            $e->getMessage(),
                            $e->getTraceAsString()
                        );
                        $pdo->rollBack();
                    }        
    
                }
            }
            else
            {
                return "An unknown error occured while deleting Snapshot. Contact Support.";
            }
        }
        else
        {
            return "An unknown error occured while deleting Snapshot. Contact Support.";
        }
    }
    return " - You haven't created a snapshot.";
}
function kametera_ssTwoDelete(array $params){
    $clientId = $params['serverusername'];
    $secret = $params['serverpassword'];

    $serverId = "";
    if (isset($params["model"]["subscriptionid"]))
    {
        $serverId = $params["model"]["subscriptionid"];
    }

    $number_of_existing_ss = kametera_alreadyCreatedSnapshots($clientId, $secret, $serverId);

    if ($params["configoptions"]["snapshots"] == 0)
    {
        return " - You haven't purchased snapshot.";
    }
    else if ($number_of_existing_ss == -1)
    {
        return " - An unknown error occured in API.";
    }
    else if ($number_of_existing_ss > 0 && $number_of_existing_ss < 5)
    {
        $snapshotId = kametera_getSnapshotID($clientId, $secret, $serverId, 2);
        $command = kametera_deleteSnapshot($clientId, $secret, $serverId, $snapshotId);

        if (isset($command)){
            if ($command["message"] == "success"){

                $data = Capsule::table('tblkamcommands')
                ->where('service_id',  $params["model"]["subscriptionid"])
                ->where('status', 'pendingx')->first();
                
                $pdo = Capsule::connection()->getPdo();
                $pdo->beginTransaction();

                if (count($data) > 0)
                {
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamtasksqueue (operation, requested_value, server_id, status) values (:operation, :requested_value, :server_id, :status)'
                        );
                    
                        $statement->execute(
                            [
                                ':operation' => "delete-ss",
                                ':requested_value' => $params["model"]["clientId"] . "-ss",
                                ':server_id' => $params["model"]["subscriptionid"],
                                ':status' => "pending",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
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
                }
                else
                {

            
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                        );
                    
                        $statement->execute(
                            [
                                ':service_id' => $serverId,
                                ':command_id' => $command["code"],
                                ':status' => "pendingx",
                                ':description' => $params["model"]["clientId"] . "-ss",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
                    } catch (\Exception $e) {
                        logModuleCall(
                            'kametera',
                            __FUNCTION__,
                            "",
                            $e->getMessage(),
                            $e->getTraceAsString()
                        );
                        $pdo->rollBack();
                    }        
    
                }
            }
            else
            {
                return "An unknown error occured while deleting Snapshot. Contact Support.";
            }
        }
        else
        {
            return "An unknown error occured while deleting Snapshot. Contact Support.";
        }
    }
    return " - You haven't created a snapshot.";

}
function kametera_ssThreeDelete(array $params){
    $clientId = $params['serverusername'];
    $secret = $params['serverpassword'];

    $serverId = "";
    if (isset($params["model"]["subscriptionid"]))
    {
        $serverId = $params["model"]["subscriptionid"];
    }

    $number_of_existing_ss = kametera_alreadyCreatedSnapshots($clientId, $secret, $serverId);

    if ($params["configoptions"]["snapshots"] == 0)
    {
        return " - You haven't purchased snapshot.";
    }
    else if ($number_of_existing_ss == -1)
    {
        return " - An unknown error occured in API.";
    }
    else if ($number_of_existing_ss > 0 && $number_of_existing_ss < 5)
    {
        $snapshotId = kametera_getSnapshotID($clientId, $secret, $serverId, 3);
        $command = kametera_deleteSnapshot($clientId, $secret, $serverId, $snapshotId);

        if (isset($command)){
            if ($command["message"] == "success"){

                $data = Capsule::table('tblkamcommands')
                ->where('service_id',  $params["model"]["subscriptionid"])
                ->where('status', 'pendingx')->first();
                
                $pdo = Capsule::connection()->getPdo();
                $pdo->beginTransaction();

                if (count($data) > 0)
                {
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamtasksqueue (operation, requested_value, server_id, status) values (:operation, :requested_value, :server_id, :status)'
                        );
                    
                        $statement->execute(
                            [
                                ':operation' => "delete-ss",
                                ':requested_value' => $params["model"]["clientId"] . "-ss",
                                ':server_id' => $params["model"]["subscriptionid"],
                                ':status' => "pending",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
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
                }
                else
                {

            
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                        );
                    
                        $statement->execute(
                            [
                                ':service_id' => $serverId,
                                ':command_id' => $command["code"],
                                ':status' => "pendingx",
                                ':description' => $params["model"]["clientId"] . "-ss",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
                    } catch (\Exception $e) {
                        logModuleCall(
                            'kametera',
                            __FUNCTION__,
                            "",
                            $e->getMessage(),
                            $e->getTraceAsString()
                        );
                        $pdo->rollBack();
                    }        
    
                }
            }
            else
            {
                return "An unknown error occured while deleting Snapshot. Contact Support.";
            }
        }
        else
        {
            return "An unknown error occured while deleting Snapshot. Contact Support.";
        }
    }
    return " - You haven't created a snapshot.";
    
}
function kametera_ssFourDelete(array $params){
    $clientId = $params['serverusername'];
    $secret = $params['serverpassword'];

    $serverId = "";
    if (isset($params["model"]["subscriptionid"]))
    {
        $serverId = $params["model"]["subscriptionid"];
    }

    $number_of_existing_ss = kametera_alreadyCreatedSnapshots($clientId, $secret, $serverId);

    if ($params["configoptions"]["snapshots"] == 0)
    {
        return " - You haven't purchased snapshot.";
    }
    else if ($number_of_existing_ss == -1)
    {
        return " - An unknown error occured in API.";
    }
    else if ($number_of_existing_ss > 0 && $number_of_existing_ss < 5)
    {
        $snapshotId = kametera_getSnapshotID($clientId, $secret, $serverId, 4);
        $command = kametera_deleteSnapshot($clientId, $secret, $serverId, $snapshotId);

        if (isset($command)){
            if ($command["message"] == "success"){

                $data = Capsule::table('tblkamcommands')
                ->where('service_id',  $params["model"]["subscriptionid"])
                ->where('status', 'pendingx')->first();
                
                $pdo = Capsule::connection()->getPdo();
                $pdo->beginTransaction();

                if (count($data) > 0)
                {
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamtasksqueue (operation, requested_value, server_id, status) values (:operation, :requested_value, :server_id, :status)'
                        );
                    
                        $statement->execute(
                            [
                                ':operation' => "delete-ss",
                                ':requested_value' => $params["model"]["clientId"] . "-ss",
                                ':server_id' => $params["model"]["subscriptionid"],
                                ':status' => "pending",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
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
                }
                else
                {

            
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                        );
                    
                        $statement->execute(
                            [
                                ':service_id' => $serverId,
                                ':command_id' => $command["code"],
                                ':status' => "pendingx",
                                ':description' => $params["model"]["clientId"] . "-ss",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
                    } catch (\Exception $e) {
                        logModuleCall(
                            'kametera',
                            __FUNCTION__,
                            "",
                            $e->getMessage(),
                            $e->getTraceAsString()
                        );
                        $pdo->rollBack();
                    }        
    
                }
            }
            else
            {
                return "An unknown error occured while deleting Snapshot. Contact Support.";
            }
        }
        else
        {
            return "An unknown error occured while deleting Snapshot. Contact Support.";
        }
    }
    return " - You haven't created a snapshot.";

}
function kametera_ssOneRevert(array $params){
    $clientId = $params['serverusername'];
    $secret = $params['serverpassword'];

    $serverId = "";
    if (isset($params["model"]["subscriptionid"]))
    {
        $serverId = $params["model"]["subscriptionid"];
    }

    $number_of_existing_ss = kametera_alreadyCreatedSnapshots($clientId, $secret, $serverId);

    if ($params["configoptions"]["snapshots"] == 0)
    {
        return " - You haven't purchased snapshot.";
    }
    else if ($number_of_existing_ss == -1)
    {
        return " - An unknown error occured in API.";
    }
    else if ($number_of_existing_ss > 0 && $number_of_existing_ss < 5)
    {
        $snapshotId = kametera_getSnapshotID($clientId, $secret, $serverId, 1);
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $snapshotId,
            "Snapshotid",
            ""
        );
        $command = kametera_revertSnapshot($clientId, $secret, $serverId, $snapshotId);

        if (isset($command)){
            if ($command["message"] == "success"){

                $data = Capsule::table('tblkamcommands')
                ->where('service_id',  $params["model"]["subscriptionid"])
                ->where('status', 'pendingx')->first();
                
                $pdo = Capsule::connection()->getPdo();
                $pdo->beginTransaction();

                if (count($data) > 0)
                {
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamtasksqueue (operation, requested_value, server_id, status) values (:operation, :requested_value, :server_id, :status)'
                        );
                    
                        $statement->execute(
                            [
                                ':operation' => "revert-ss",
                                ':requested_value' => $params["model"]["userid"] . "-ss",
                                ':server_id' => $params["model"]["subscriptionid"],
                                ':status' => "pending",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
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
                }
                else
                {
            
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                        );
                    
                        $statement->execute(
                            [
                                ':service_id' => $serverId,
                                ':command_id' => $command["code"],
                                ':status' => "pendingx",
                                ':description' => $params["model"]["userid"] . "-ss",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
                    } catch (\Exception $e) {
                        logModuleCall(
                            'kametera',
                            __FUNCTION__,
                            "",
                            $e->getMessage(),
                            $e->getTraceAsString()
                        );
                        $pdo->rollBack();
                    }        
    
                }
            }
            else
            {
                return "An unknown error occured while reverting Snapshot. Contact Support.";
            }
        }
        else
        {
            return "An unknown error occured while reverting Snapshot. Contact Support.";
        }
    }
    return " - You haven't created a snapshot.";

}
function kametera_ssTwoRevert(array $params){
    $clientId = $params['serverusername'];
    $secret = $params['serverpassword'];

    $serverId = "";
    if (isset($params["model"]["subscriptionid"]))
    {
        $serverId = $params["model"]["subscriptionid"];
    }

    $number_of_existing_ss = kametera_alreadyCreatedSnapshots($clientId, $secret, $serverId);

    if ($params["configoptions"]["snapshots"] == 0)
    {
        return " - You haven't purchased snapshot.";
    }
    else if ($number_of_existing_ss == -1)
    {
        return " - An unknown error occured in API.";
    }
    else if ($number_of_existing_ss > 0 && $number_of_existing_ss < 5)
    {
        $snapshotId = kametera_getSnapshotID($clientId, $secret, $serverId, 2);
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $snapshotId,
            "Snapshotid",
            ""
        );
        $command = kametera_revertSnapshot($clientId, $secret, $serverId, $snapshotId);
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $command,
            "Command ID",
            ""
        );
        if (isset($command)){
            if ($command["message"] == "success"){

                $data = Capsule::table('tblkamcommands')
                ->where('service_id',  $params["model"]["subscriptionid"])
                ->where('status', 'pendingx')->first();
                
                $pdo = Capsule::connection()->getPdo();
                $pdo->beginTransaction();

                if (count($data) > 0)
                {
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamtasksqueue (operation, requested_value, server_id, status) values (:operation, :requested_value, :server_id, :status)'
                        );
                    
                        $statement->execute(
                            [
                                ':operation' => "revert-ss",
                                ':requested_value' => $params["model"]["clientId"] . "-ss",
                                ':server_id' => $params["model"]["subscriptionid"],
                                ':status' => "pending",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
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
                }
                else
                {

            
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                        );
                    
                        $statement->execute(
                            [
                                ':service_id' => $serverId,
                                ':command_id' => $command["code"],
                                ':status' => "pendingx",
                                ':description' => $params["model"]["clientId"] . "-ss",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
                    } catch (\Exception $e) {
                        logModuleCall(
                            'kametera',
                            __FUNCTION__,
                            "",
                            $e->getMessage(),
                            $e->getTraceAsString()
                        );
                        $pdo->rollBack();
                    }        
    
                }
            }
            else
            {
                return "An unknown error occured while reverting Snapshot. Contact Support.";
            }
        }
        else
        {
            return "An unknown error occured while reverting Snapshot. Contact Support.";
        }
    }
    return " - You haven't created a snapshot.";

}
function kametera_ssThreeRevert(array $params){
    $clientId = $params['serverusername'];
    $secret = $params['serverpassword'];

    $serverId = "";
    if (isset($params["model"]["subscriptionid"]))
    {
        $serverId = $params["model"]["subscriptionid"];
    }

    $number_of_existing_ss = kametera_alreadyCreatedSnapshots($clientId, $secret, $serverId);

    if ($params["configoptions"]["snapshots"] == 0)
    {
        return " - You haven't purchased snapshot.";
    }
    else if ($number_of_existing_ss == -1)
    {
        return " - An unknown error occured in API.";
    }
    else if ($number_of_existing_ss > 0 && $number_of_existing_ss < 5)
    {
        $snapshotId = kametera_getSnapshotID($clientId, $secret, $serverId, 3);
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $snapshotId,
            "Snapshotid",
            ""
        );
        $command = kametera_revertSnapshot($clientId, $secret, $serverId, $snapshotId);
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $command,
            "Command ID",
            ""
        );
        if (isset($command)){
            if ($command["message"] == "success"){

                $data = Capsule::table('tblkamcommands')
                ->where('service_id',  $params["model"]["subscriptionid"])
                ->where('status', 'pendingx')->first();
                
                $pdo = Capsule::connection()->getPdo();
                $pdo->beginTransaction();

                if (count($data) > 0)
                {
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamtasksqueue (operation, requested_value, server_id, status) values (:operation, :requested_value, :server_id, :status)'
                        );
                    
                        $statement->execute(
                            [
                                ':operation' => "revert-ss",
                                ':requested_value' => $params["model"]["clientId"] . "-ss",
                                ':server_id' => $params["model"]["subscriptionid"],
                                ':status' => "pending",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
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
                }
                else
                {

            
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                        );
                    
                        $statement->execute(
                            [
                                ':service_id' => $serverId,
                                ':command_id' => $command["code"],
                                ':status' => "pendingx",
                                ':description' => $params["model"]["clientId"] . "-ss",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
                    } catch (\Exception $e) {
                        logModuleCall(
                            'kametera',
                            __FUNCTION__,
                            "",
                            $e->getMessage(),
                            $e->getTraceAsString()
                        );
                        $pdo->rollBack();
                    }        
    
                }
            }
            else
            {
                return "An unknown error occured while reverting Snapshot. Contact Support.";
            }
        }
        else
        {
            return "An unknown error occured while reverting Snapshot. Contact Support.";
        }
    }
    return " - You haven't created a snapshot.";

}
function kametera_ssFourRevert(array $params){
    $clientId = $params['serverusername'];
    $secret = $params['serverpassword'];

    $serverId = "";
    if (isset($params["model"]["subscriptionid"]))
    {
        $serverId = $params["model"]["subscriptionid"];
    }

    $number_of_existing_ss = kametera_alreadyCreatedSnapshots($clientId, $secret, $serverId);

    if ($params["configoptions"]["snapshots"] == 0)
    {
        return " - You haven't purchased snapshot.";
    }
    else if ($number_of_existing_ss == -1)
    {
        return " - An unknown error occured in API.";
    }
    else if ($number_of_existing_ss > 0 && $number_of_existing_ss < 5)
    {
        $snapshotId = kametera_getSnapshotID($clientId, $secret, $serverId, 4);
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $snapshotId,
            "Snapshotid",
            ""
        );
        $command = kametera_revertSnapshot($clientId, $secret, $serverId, $snapshotId);
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $command,
            "Command ID",
            ""
        );
        if (isset($command)){
            if ($command["message"] == "success"){

                $data = Capsule::table('tblkamcommands')
                ->where('service_id',  $params["model"]["subscriptionid"])
                ->where('status', 'pendingx')->first();
                
                $pdo = Capsule::connection()->getPdo();
                $pdo->beginTransaction();

                if (count($data) > 0)
                {
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamtasksqueue (operation, requested_value, server_id, status) values (:operation, :requested_value, :server_id, :status)'
                        );
                    
                        $statement->execute(
                            [
                                ':operation' => "revert-ss",
                                ':requested_value' => $params["model"]["clientId"] . "-ss",
                                ':server_id' => $params["model"]["subscriptionid"],
                                ':status' => "pending",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
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
                }
                else
                {

            
                    try {
                        $statement = $pdo->prepare(
                            'insert into tblkamcommands (service_id, command_id , status, description) values (:service_id, :command_id, :status, :description)'
                        );
                    
                        $statement->execute(
                            [
                                ':service_id' => $serverId,
                                ':command_id' => $command["code"],
                                ':status' => "pendingx",
                                ':description' => $params["model"]["clientId"] . "-ss",
                            ]
                        );
                    
                        $pdo->commit();
                        return "success";
                    } catch (\Exception $e) {
                        logModuleCall(
                            'kametera',
                            __FUNCTION__,
                            "",
                            $e->getMessage(),
                            $e->getTraceAsString()
                        );
                        $pdo->rollBack();
                    }        
    
                }
            }
            else
            {
                return "An unknown error occured while reverting Snapshot. Contact Support.";
            }
        }
        else
        {
            return "An unknown error occured while reverting Snapshot. Contact Support.";
        }
    }
    return " - You haven't created a snapshot.";

}

/**
 * returns purchase backup page
 */
function kametera_backup($vars)
{
    // $command = 'GetProducts';
    // $postData = array(
    //     'pid' => $vars["pid"],
    // );

    // $results = localAPI($command, $postData);

    $url = "";

    if ($_SERVER["HTTPS"] == "on")
    {
        $url = "https://". $_SERVER["HTTP_HOST"] ."/upgrade.php?type=configoptions&id=" . $vars["serviceid"];
    }
    else
    {
        $url = "http://". $_SERVER["HTTP_HOST"] ."/upgrade.php?type=configoptions&id=" . $vars["serviceid"];
    }
    // $imageId = explode("|", $vars["configoptions"]["images"]);
    // $dbresults = Capsule::table("tblproductconfigoptionssub")
    // ->where("optionname", 'like', $imageId[0].'%')->first();

    // $minSize = 0;
    // if (count($dbresults) > 0)
    // {
    //     $size = explode("Minimum Required Disk ", $dbresults->optionname);
    //     $minSize = $size[1];
    // }
    return array(
        'templatefile' => 'changebackup',
        'breadcrumb' => array('upgrade.php?type=configoptions&id='. $vars["serviceid"] => 'Backup Subscription'),
        'vars' => array(
            'url' => $url,
            'coperation' => 'backup'
        ),
    );
}
/**
 * returns purchase snapshots page
 */
function kametera_confSnapshots($vars)
{
    // $command = 'GetProducts';
    // $postData = array(
    //     'pid' => $vars["pid"],
    // );

    // $results = localAPI($command, $postData);

    $url = "";

    if ($_SERVER["HTTPS"] == "on")
    {
        $url = "https://". $_SERVER["HTTP_HOST"] ."/upgrade.php?type=configoptions&id=" . $vars["serviceid"];
    }
    else
    {
        $url = "http://". $_SERVER["HTTP_HOST"] ."/upgrade.php?type=configoptions&id=" . $vars["serviceid"];
    }
    // $imageId = explode("|", $vars["configoptions"]["images"]);
    // $dbresults = Capsule::table("tblproductconfigoptionssub")
    // ->where("optionname", 'like', $imageId[0].'%')->first();

    // $minSize = 0;
    // if (count($dbresults) > 0)
    // {
    //     $size = explode("Minimum Required Disk ", $dbresults->optionname);
    //     $minSize = $size[1];
    // }
    return array(
        'templatefile' => 'purchasesnapshots',
        'breadcrumb' => array('upgrade.php?type=configoptions&id='. $vars["serviceid"] => 'Upgrade / Downgrade Snapshots Allocation'),
        'vars' => array(
            'url' => $url,
            'coperation' => 'ss'
        ),
    );
}
/**
 * returns configure CPU page
 */
function kametera_configureCPU($vars)
{
    // $command = 'GetProducts';
    // $postData = array(
    //     'pid' => $vars["pid"],
    // );

    // $results = localAPI($command, $postData);

    $url = "";

    if ($_SERVER["HTTPS"] == "on")
    {
        $url = "https://". $_SERVER["HTTP_HOST"] ."/upgrade.php?type=configoptions&id=" . $vars["serviceid"];
    }
    else
    {
        $url = "http://". $_SERVER["HTTP_HOST"] ."/upgrade.php?type=configoptions&id=" . $vars["serviceid"];
    }
    // $imageId = explode("|", $vars["configoptions"]["images"]);
    // $dbresults = Capsule::table("tblproductconfigoptionssub")
    // ->where("optionname", 'like', $imageId[0].'%')->first();

    // $minSize = 0;
    // if (count($dbresults) > 0)
    // {
    //     $size = explode("Minimum Required Disk ", $dbresults->optionname);
    //     $minSize = $size[1];
    // }
    return array(
        'templatefile' => 'configureCPU',
        'breadcrumb' => array('upgrade.php?type=configoptions&id='. $vars["serviceid"] => 'Configure CPU'),
        'vars' => array(
            'url' => $url,
            'coperation' => 'cpu'
        ),
    );
}
/**
 * returns configure RAM page
 */
function kametera_configureRAM($vars)
{
    // $command = 'GetProducts';
    // $postData = array(
    //     'pid' => $vars["pid"],
    // );

    // $results = localAPI($command, $postData);

    $url = "";

    if ($_SERVER["HTTPS"] == "on")
    {
        $url = "https://". $_SERVER["HTTP_HOST"] ."/upgrade.php?type=configoptions&id=" . $vars["serviceid"];
    }
    else
    {
        $url = "http://". $_SERVER["HTTP_HOST"] ."/upgrade.php?type=configoptions&id=" . $vars["serviceid"];
    }
    // $imageId = explode("|", $vars["configoptions"]["images"]);
    // $dbresults = Capsule::table("tblproductconfigoptionssub")
    // ->where("optionname", 'like', $imageId[0].'%')->first();

    // $minSize = 0;
    // if (count($dbresults) > 0)
    // {
    //     $size = explode("Minimum Required Disk ", $dbresults->optionname);
    //     $minSize = $size[1];
    // }
    return array(
        'templatefile' => 'configureRAM',
        'breadcrumb' => array('upgrade.php?type=configoptions&id='. $vars["serviceid"] => 'Configure RAM'),
        'vars' => array(
            'url' => $url,
            'coperation' => 'ram'
        ),
    );
}
/**
 * returns configure configure Primary Disk page
 */
function kametera_configurePrimaryDisk($vars)
{
    // $command = 'GetProducts';
    // $postData = array(
    //     'pid' => $vars["pid"],
    // );

    // $results = localAPI($command, $postData);

    $url = "";

    if ($_SERVER["HTTPS"] == "on")
    {
        $url = "https://". $_SERVER["HTTP_HOST"] ."/upgrade.php?type=configoptions&id=" . $vars["serviceid"];
    }
    else
    {
        $url = "http://". $_SERVER["HTTP_HOST"] ."/upgrade.php?type=configoptions&id=" . $vars["serviceid"];
    }
    // $imageId = explode("|", $vars["configoptions"]["images"]);
    // $dbresults = Capsule::table("tblproductconfigoptionssub")
    // ->where("optionname", 'like', $imageId[0].'%')->first();

    // $minSize = 0;
    // if (count($dbresults) > 0)
    // {
    //     $size = explode("Minimum Required Disk ", $dbresults->optionname);
    //     $minSize = $size[1];
    // }
    return array(
        'templatefile' => 'configurePrimaryDisk',
        'breadcrumb' => array('upgrade.php?type=configoptions&id='. $vars["serviceid"] => 'Configure Primary Disk'),
        'vars' => array(
            'url' => $url,
            'coperation' => 'pdisk'
        ),
    );
}
/**
 * returns configure Secondary Disk page
 */
function kametera_configureSecondaryDiskA($vars)
{
    // $command = 'GetProducts';
    // $postData = array(
    //     'pid' => $vars["pid"],
    // );

    // $results = localAPI($command, $postData);

    $url = "";

    if ($_SERVER["HTTPS"] == "on")
    {
        $url = "https://". $_SERVER["HTTP_HOST"] ."/upgrade.php?type=configoptions&id=" . $vars["serviceid"];
    }
    else
    {
        $url = "http://". $_SERVER["HTTP_HOST"] ."/upgrade.php?type=configoptions&id=" . $vars["serviceid"];
    }
    // $imageId = explode("|", $vars["configoptions"]["images"]);
    // $dbresults = Capsule::table("tblproductconfigoptionssub")
    // ->where("optionname", 'like', $imageId[0].'%')->first();

    // $minSize = 0;
    // if (count($dbresults) > 0)
    // {
    //     $size = explode("Minimum Required Disk ", $dbresults->optionname);
    //     $minSize = $size[1];
    // }
    return array(
        'templatefile' => 'configureSecondaryDiskA',
        'breadcrumb' => array('upgrade.php?type=configoptions&id='. $vars["serviceid"] => 'Configure Secondary Disk A'),
        'vars' => array(
            'url' => $url,
            'coperation' => 'disk1'
        ),
    );
}
/**
 * returns configure Secondary Disk page
 */
function kametera_configureSecondaryDiskB($vars)
{
    // $command = 'GetProducts';
    // $postData = array(
    //     'pid' => $vars["pid"],
    // );

    // $results = localAPI($command, $postData);

    $url = "";

    if ($_SERVER["HTTPS"] == "on")
    {
        $url = "https://". $_SERVER["HTTP_HOST"] ."/upgrade.php?type=configoptions&id=" . $vars["serviceid"];
    }
    else
    {
        $url = "http://". $_SERVER["HTTP_HOST"] ."/upgrade.php?type=configoptions&id=" . $vars["serviceid"];
    }
    // $imageId = explode("|", $vars["configoptions"]["images"]);
    // $dbresults = Capsule::table("tblproductconfigoptionssub")
    // ->where("optionname", 'like', $imageId[0].'%')->first();

    // $minSize = 0;
    // if (count($dbresults) > 0)
    // {
    //     $size = explode("Minimum Required Disk ", $dbresults->optionname);
    //     $minSize = $size[1];
    // }
    return array(
        'templatefile' => 'configureSecondaryDiskB',
        'breadcrumb' => array('upgrade.php?type=configoptions&id='. $vars["serviceid"] => 'Configure Secondary Disk B'),
        'vars' => array(
            'url' => $url,
            'coperation' => 'disk2'
        ),
    );
}
/**
 * returns clone server page
 */
function kametera_cloneServer($vars)
{
    $command = 'GetProducts';
    $postData = array(
        'pid' => $vars["pid"],
    );

    logModuleCall(
        'kametera',
        __FUNCTION__,
        $vars,
        "",
        ""
    );

    $results = localAPI($command, $postData);

    // $imageId = explode("|", $vars["configoptions"]["images"]);
    // $dbresults = Capsule::table("tblproductconfigoptionssub")
    // ->where("optionname", 'like', $imageId[0].'%')->first();

    // $minSize = 0;
    // if (count($dbresults) > 0)
    // {
    //     $size = explode("Minimum Required Disk ", $dbresults->optionname);
    //     $minSize = $size[1];
    // }
    return array(
        'templatefile' => 'clone',
        'breadcrumb' => array('clientarea.php?action=productdetails&id='. $_REQUEST["id"] . '&modop=custom&a=cloneServer' => 'Clone Server'),
        'vars' => array(
            'url' => $results["products"]["product"][0]["product_url"],
            'datacenter' => $vars["configoptions"]["datacenters"],
            'cpu' => $vars["configoptions"]["cpus"],          
            'ram' => $vars["configoptions"]["ram"],         
            'traffic' => $vars["configoptions"]["traffic"],         
            'network' => $vars["configoptions"]["networks"],
            'serverid' => $vars["model"]["subscriptionid"],
            'disk0' => $vars["configoptions"]["disks"],
            'disk1' => $vars["configoptions"]["disk1"],
            'disk2' => $vars["configoptions"]["disk2"],
            // 'minSize' => $minSize
        ),
    );
}
/**
 * Client Side Resize Harddisk Size
 */
function kametera_resizeDisk(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}
/**
 * Client Side RAM Configuration Option
 */
// function kametera_configureRAM(array $params)
// {
//     try {
//         // Call the service's function, using the values provided by WHMCS in
//         // `$params`.
//     } catch (Exception $e) {
//         // Record the error in WHMCS's module log.
//         logModuleCall(
//             'kametera',
//             __FUNCTION__,
//             $params,
//             $e->getMessage(),
//             $e->getTraceAsString()
//         );

//         return $e->getMessage();
//     }

//     return 'success';
// }
/**
 * Client Side CPU Configuration Option
 */

// function kametera_configureCPU(array $params)
// {
//     try {
//         // Call the service's function, using the values provided by WHMCS in
//         // `$params`.
//     } catch (Exception $e) {
//         // Record the error in WHMCS's module log.
//         logModuleCall(
//             'kametera',
//             __FUNCTION__,
//             $params,
//             $e->getMessage(),
//             $e->getTraceAsString()
//         );

//         return $e->getMessage();
//     }

//     return 'success';
// }

/**
 * Client Side Start Server
 */

function kametera_startServer(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
        $clientId = $params['serverusername'];
        $secret = $params['serverpassword'];

        $serverId = "";
        if (isset($params["model"]["subscriptionid"]))
        {
            $serverId = $params["model"]["subscriptionid"];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$serverId}/power");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "AuthClientId: {$clientId}",
            "AuthSecret: {$secret}",
            "Content-Type: application/x-www-form-urlencoded"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'power' => 'on'
        )));
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status == 200)
        {
            return 'cuccess Server start initiated. This operation may take 1-2 minutes. Refresh page to see latest power status.';
        }
        else
        {
            $e = json_decode($body);
            return $e->errors[0]->info;
        }
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}
/**
 * Client Side Restart Server
 */

function kametera_restartServer(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
        $clientId = $params['serverusername'];
        $secret = $params['serverpassword'];

        $serverId = "";
        if (isset($params["model"]["subscriptionid"]))
        {
            $serverId = $params["model"]["subscriptionid"];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$serverId}/power");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "AuthClientId: {$clientId}",
            "AuthSecret: {$secret}",
            "Content-Type: application/x-www-form-urlencoded"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'power' => 'restart'
        )));
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status == 200)
        {
            return 'cuccess Server restart initiated. This operation may take 1-2 minutes. Refresh page to see latest power status.';
        }
        else
        {
            $e = json_decode($body);
            return $e->errors[0]->info;
        }
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}

/**
 * Client Side Shutdown Server
 */

function kametera_shutdownServer(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
        $clientId = $params['serverusername'];
        $secret = $params['serverpassword'];

        $serverId = "";
        if (isset($params["model"]["subscriptionid"]))
        {
            $serverId = $params["model"]["subscriptionid"];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$serverId}/power");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "AuthClientId: {$clientId}",
            "AuthSecret: {$secret}",
            "Content-Type: application/x-www-form-urlencoded"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'power' => 'off'
        )));
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status == 200)
        {
            return 'cuccess Server shutdown initiated. This operation may take 1-2 minutes. Refresh page to see latest power status.';
        }
        else
        {
            $e = json_decode($body);
            return $e->errors[0]->info;
        }
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}
/**
 * Client Side Terminate Server
 */

// function kametera_terminateServer(array $params)
// {
//     try {
//         // Call the service's terminate function, using the values provided by
//         // WHMCS in `$params`.

//         $clientId = $params['serverusername'];
//         $secret = $params['serverpassword'];

//         $serverId = "";
//         if (isset($params["model"]["subscriptionid"]))
//         {
//             $serverId = $params["model"]["subscriptionid"];
//         }
//         $confirm = 1;
//         $force = 1;

//         if ($serverId != "")
//         {
//             $ch = curl_init();
//             curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/server/{$serverId}/terminate");
//             curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//                 "AuthClientId: {$clientId}",
//                 "AuthSecret: {$secret}",
//                 "Content-Type: application/x-www-form-urlencoded"
//             ));
//             curl_setopt($ch,
//             CURLOPT_RETURNTRANSFER, TRUE);
//             curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
//             curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
//                 'confirm'   => $confirm,
//                 'force'   => $force
//             )));
//             $body = curl_exec($ch);
//             $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//             curl_close($ch);
    
//             if ($status == 200)
//             {
//                 kametera_updateSubscriptionID($params["serviceid"], "");
//                 $fieldid = kametera_createCommandIdField($params["pid"]);
//                 kametera_saveCommandIdField($fieldid, $params["serviceid"], "");
                
//                 return 'success';
//             }
//             else
//             {
//                 $e = json_decode($body);
//                 return $e->errors[0]->info;
//             }
    
//         }
//         else
//         {
//             return "No subscription exists in Kametera.";
//         }

//     } catch (Exception $e) {
//         // Record the error in WHMCS's module log.
//         logModuleCall(
//             'kametera',
//             __FUNCTION__,
//             $params,
//             $e->getMessage(),
//             $e->getTraceAsString()
//         );

//         return $e->getMessage();
//     }
// }
/**
 * Client Side Clone Server
 */

// function kametera_cloneServer(array $params)
// {
//     try {
//         // Call the service's function, using the values provided by WHMCS in
//         // `$params`.
//     } catch (Exception $e) {
//         // Record the error in WHMCS's module log.
//         logModuleCall(
//             'kametera',
//             __FUNCTION__,
//             $params,
//             $e->getMessage(),
//             $e->getTraceAsString()
//         );

//         return $e->getMessage();
//     }

//     return 'success';
// }
/**
 *  This function fetches status of command executed
 */
function kametera_getCommandStatus($clientId, $secret, $commandId){

    if (isset($commandId))
    {
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,
        "https://console.kamatera.com/service/queue/{$commandId}");
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
            $response = json_decode($body);
            return $response->status;
        }
        else
        {
            $e = json_decode($body);
            return $e->errors[0]->info;
        }
        
    }
    else
    {
        return "Command ID must be valid to check provision status.";
    }
}
/**
 * This function checks Kametera Server Provision status on admin side
 */
function kametera_ProvisionStatus(array $params){

    if ($params["customfields"]["Command ID"] != "")
    {
        $clientId = $params['serverusername'];
        $secret = $params['serverpassword'];
    
        $commandId = $params["customfields"]["Command ID"];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,
        "https://console.kamatera.com/service/queue/{$commandId}");
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
            $response = json_decode($body);
            if ($response->status == "complete")
            {
                return 'success';
            }
            else
            {
                return "Server provision status returned by Kametera API >> " . $response->status;
            }
        }
        else
        {
            $e = json_decode($body);
            return $e->errors[0]->info;
        }
        
    }
    else
    {
        return "Command ID must be valid to check provision status.";
    }
}
/**
 * Custom function for performing an additional action.
 *
 * You can define an unlimited number of custom functions in this way.
 *
 * Similar to all other module call functions, they should either return
 * 'success' or an error message to be displayed.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see kametera_AdminCustomButtonArray()
 *
 * @return string "success" or an error message
 */
function kametera_Refresh(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.

        $subid = kametera_subscriptionIDVerification($params["serviceid"]);

        if ($subid != 0)
        {
            $subscription_id = kametera_fetchServerID($params['serverusername'], $params['serverpassword'], $params["serviceid"]); 
            if ($subscription_id != 0)
            {
                kametera_updateSubscriptionID($params["serviceid"], $subscription_id);
                kametera_assignIP($params['serverusername'], $params['serverpassword'], $params['serviceid'], $subscription_id);
            }
        }
        else
        {
            $subscription_id = kametera_fetchServerID($params['serverusername'], $params['serverpassword'], $params['serviceid']); 
            if ($subscription_id != 0)
            {
                kametera_assignIP($params['serverusername'], $params['serverpassword'], $params['serviceid'], $subscription_id);                
            }
        }
        return 'success';

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}

/**
 * Custom function for performing an additional action.
 *
 * You can define an unlimited number of custom functions in this way.
 *
 * Similar to all other module call functions, they should either return
 * 'success' or an error message to be displayed.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see kametera_ClientAreaCustomButtonArray()
 *
 * @return string "success" or an error message
 */
function kametera_actionOneFunction(array $params)
{
    try {
        $buttonarray = array(
            "Reboot Server" => "reboot",
           );
           return $buttonarray;
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Admin services tab additional fields.
 *
 * Define additional rows and fields to be displayed in the admin area service
 * information and management page within the clients profile.
 *
 * Supports an unlimited number of additional field labels and content of any
 * type to output.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see kametera_AdminServicesTabFieldsSave()
 *
 * @return array
 */
function kametera_AdminServicesTabFields(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
        $response = array();

        // Return an array based on the function's response.
        // return array(
        //     'Command ID' => '<input type="text" name="kametera_commandid" '
        //         . 'value="' . htmlspecialchars($response['textvalue']) . '" />'
        // );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        // In an error condition, simply return no additional fields to display.
    }

    return array();
}

/**
 * Execute actions upon save of an instance of a product/service.
 *
 * Use to perform any required actions upon the submission of the admin area
 * product management form.
 *
 * It can also be used in conjunction with the AdminServicesTabFields function
 * to handle values submitted in any custom fields which is demonstrated here.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see kametera_AdminServicesTabFields()
 */
function kametera_AdminServicesTabFieldsSave(array $params)
{
    logModuleCall(
        'kametera',
        __FUNCTION__,
        $params,
        "",
        ""
    );
    // Fetch form submission variables.
    $originalFieldValue = isset($_REQUEST['kametera_original_commandid'])
        ? $_REQUEST['kametera_original_commandid']
        : '';

    $newFieldValue = isset($_REQUEST['kametera_commandid'])
        ? $_REQUEST['kametera_commandid']
        : '';

    // Look for a change in value to avoid making unnecessary service calls.
    if ($originalFieldValue != $newFieldValue) {
        try {
            // Call the service's function, using the values provided by WHMCS
            // in `$params`.

        } catch (Exception $e) {
            // Record the error in WHMCS's module log.
            logModuleCall(
                'kametera',
                __FUNCTION__,
                $params,
                $e->getMessage(),
                $e->getTraceAsString()
            );

            // Otherwise, error conditions are not supported in this operation.
        }
    }
}

/**
 * Perform single sign-on for a given instance of a product/service.
 *
 * Called when single sign-on is requested for an instance of a product/service.
 *
 * When successful, returns a URL to which the user should be redirected.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function kametera_ServiceSingleSignOn(array $params)
{
    try {
        // Call the service's single sign-on token retrieval function, using the
        // values provided by WHMCS in `$params`.
        $response = array();

        return array(
            'success' => true,
            'redirectTo' => $response['redirectUrl'],
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }
}

/**
 * Perform single sign-on for a server.
 *
 * Called when single sign-on is requested for a server assigned to the module.
 *
 * This differs from ServiceSingleSignOn in that it relates to a server
 * instance within the admin area, as opposed to a single client instance of a
 * product/service.
 *
 * When successful, returns a URL to which the user should be redirected to.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function kametera_AdminSingleSignOn(array $params)
{
    try {
        // Call the service's single sign-on admin token retrieval function,
        // using the values provided by WHMCS in `$params`.
        $response = array();

        return array(
            'success' => true,
            'redirectTo' => $response['redirectUrl'],
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }
}
/**
 * This function fetches server id
 */
function kametera_fetchServerID($clientId, $secret, $serviceid){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://console.kamatera.com/service/servers");
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
        $servers = json_decode($body);
        $server_id = 0;
        for ($i = 0 ; $i < count($servers) ; $i++)
        {
            if (preg_replace("/[^0-9]/", "", $servers[$i]->name) == $serviceid)
            {
                $server_id = $servers[$i]->id;
                break;
            }
        }
        return $server_id;    
    }
    else
    {
        logModuleCall(
            'kametera',
            __FUNCTION__,
            "Kametera API error occured while fetching servers list.",
            "",
            ""
        ); 
    }
}

/**
 * Check if subscription has been assigned or not
 */
function kametera_subscriptionIDVerification($service_id)
{
    $results = Capsule::table("tblhosting")
                ->where("id", $service_id)
                ->where("subscriptionid", "=", "")->first();

    if (count($results) > 0)
    {
        return $results->id;
    }
    else
    {
        return 0;
    }
}
/**
 * This function updates assigned IP addresses.
 */
function kametera_assignIP($clientId, $secret, $service_id, $serverId){
    
   
    try {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,
        "https://console.kamatera.com/service/server/{$serverId}");
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
            $res = json_decode($body);
            logModuleCall(
                'kametera',
                __FUNCTION__,
                $res->networks[0]->ips[0],
                "",
                ""
            );
            if (isset($res->networks[0]->ips[0]))
            {
                $r = Capsule::table('tblhosting')
                ->where('id', $service_id)
                ->where('subscriptionid', $serverId)
                ->update(
                    [
                        'assignedips' => $res->networks[0]->ips[0],
                    ]
                );
    
                if (count($r) > 0)
                {
                    return true;
                }
                else
                {
                    return false;
                }    
            }
            else
            {
                return "Error: IP is not assigned.";
            }
        }
        else
        {
            $e = json_decode($body);
            return $e->errors[0]->info;
        }

    } catch (Exception $e) {
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }
}
/**
 * This function updates subscription id with serverid.
 */
function kametera_updateSubscriptionID($service_id, $subscription_id){
    
   
    try {
        $r = Capsule::table('tblhosting')
            ->where('id', $service_id)
            ->update(
                [
                    'subscriptionid' => $subscription_id,
                ]
            );

        if (count($r) > 0)
        {
            return true;
        }
        else
        {
            return false;
        }


    } catch (Exception $e) {
        logModuleCall(
            'kametera',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }
}
/**
 * Client area output logic handling.
 *
 * This function is used to define module specific client area output. It should
 * return an array consisting of a template file and optional additional
 * template variables to make available to that template.
 *
 * The template file you return can be one of two types:
 *
 * * tabOverviewModuleOutputTemplate - The output of the template provided here
 *   will be displayed as part of the default product/service client area
 *   product overview page.
 *
 * * tabOverviewReplacementTemplate - Alternatively using this option allows you
 *   to entirely take control of the product/service overview page within the
 *   client area.
 *
 * Whichever option you choose, extra template variables are defined in the same
 * way. This demonstrates the use of the full replacement.
 *
 * Please Note: Using tabOverviewReplacementTemplate means you should display
 * the standard information such as pricing and billing details in your custom
 * template or they will not be visible to the end user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function kametera_ClientArea(array $params)
{
    // Determine the requested action and set service call parameters based on
    // the action.

    if ($_REQUEST["action"] == "productdetails" && $_REQUEST["modop"] != "custom")
    {
        $subid = kametera_subscriptionIDVerification($params['serviceid']);

        if ($subid != 0)
        {
            $subscription_id = kametera_fetchServerID($params['serverusername'], $params['serverpassword'], $params['serviceid']); 
            if ($subscription_id != 0)
            {
                kametera_updateSubscriptionID($_REQUEST["id"], $subscription_id);
                if ($subscription_id != "")
                {
                    kametera_assignIP($params['serverusername'], $params['serverpassword'], $params['serviceid'], $subscription_id);                
                }
            }
        }
        else
        {
            $subscription_id = kametera_fetchServerID($params['serverusername'], $params['serverpassword'], $params['serviceid']); 
            if ($subscription_id != 0)
            {
                if ($subscription_id != "")
                {
                    kametera_assignIP($params['serverusername'], $params['serverpassword'], $params['serviceid'], $subscription_id);                
                }
            }
        }
    }


        $requestedAction = '';

        if (isset($_REQUEST['customAction']))
        {
            $requestedAction = $_REQUEST['customAction'];
        }
        elseif (isset($_REQUEST['a']))
        {
            $requestedAction = $_REQUEST['a'];
        }
    
        if ($requestedAction == 'manage') {
            $serviceAction = 'get_usage';
            $templateFile = 'templates/manage.tpl';
        } elseif($requestedAction == 'configureCPU') {
            $serviceAction = 'get_stats';
            $templateFile = 'templates/configureCPU.tpl';
        }

    
        try {
            // Call the service's function based on the request action, using the
            // values provided by WHMCS in `$params`.
            $response = array();
    
            $extraVariable1 = 'abc';
            $extraVariable2 = '123';
    
            return array(
                'tabOverviewModuleOutputTemplate' => $templateFile,
                'templateVariables' => array(
                    'extraVariable1' => $extraVariable1,
                    'extraVariable2' => $extraVariable2,
                ),
            );
        } catch (Exception $e) {
            // Record the error in WHMCS's module log.
            logModuleCall(
                'kametera',
                __FUNCTION__,
                $params,
                $e->getMessage(),
                $e->getTraceAsString()
            );
    
            // In an error condition, display an error page.
            return array(
                'tabOverviewReplacementTemplate' => 'error.tpl',
                'templateVariables' => array(
                    'usefulErrorHelper' => $e->getMessage(),
                ),
            );
        }
    
    
}

