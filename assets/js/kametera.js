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
jQuery(document).ready(function(){

    jQuery("#yes").click(function(){
        if(jQuery("#cconfirm").is(":checked")){
            window.location.href = jQuery("#url").val();
            jQuery("label").css("border-color", "#bcbcbc");
        }
        else
        {
            jQuery("label").css("border-color", "red");
        }
    });

    if(window.location.href.indexOf("configureCPU") > -1) {
        jQuery("#fullpage-overlay").remove();
        localStorage.setItem("coperation", "cpu");
    }
    else if(window.location.href.indexOf("configureRAM") > -1) {
        jQuery("#fullpage-overlay").remove();
        localStorage.setItem("coperation", "ram");
    }
    else if(window.location.href.indexOf("configurePrimaryDisk") > -1) {
        jQuery("#fullpage-overlay").remove();
        localStorage.setItem("coperation", "pdisk");
    }
    else if(window.location.href.indexOf("configureSecondaryDiskA") > -1) {
        jQuery("#fullpage-overlay").remove();
        localStorage.setItem("coperation", "disk1");
    }
    else if(window.location.href.indexOf("configureSecondaryDiskB") > -1) {
        jQuery("#fullpage-overlay").remove();
        localStorage.setItem("coperation", "disk2");
    }
    else if(window.location.href.indexOf("confSnapshots") > -1) {
        jQuery("#fullpage-overlay").remove();
        localStorage.setItem("coperation", "ss");
    }
    else if(window.location.href.indexOf("backup") > -1) {
        jQuery("#fullpage-overlay").remove();
        localStorage.setItem("coperation", "backup");
    }
    else if(window.location.href.indexOf("snapshotsList") > -1) {
        jQuery("#fullpage-overlay").remove();
    }
    else
    {
        if (window.location.href.indexOf("upgrade.php?type=configoptions") < 0)
        {
            localStorage.removeItem("coperation");
        }
    }
    if(window.location.href.indexOf("cloneServer") > -1) {
        jQuery("#fullpage-overlay").remove();
        localStorage.setItem("type", "clone");
        localStorage.setItem("datacenter", jQuery("#datacenter").val());
        localStorage.setItem("cpu", jQuery("#cpu").val());
        localStorage.setItem("ram", jQuery("#ram").val());
        localStorage.setItem("traffic", jQuery("#traffic").val());
        localStorage.setItem("network", jQuery("#network").val());
        localStorage.setItem("serverid", jQuery("#serverid").val());
        localStorage.setItem("disk0", jQuery("#disk0").val());
        localStorage.setItem("disk1", jQuery("#disk1").val());
        localStorage.setItem("disk2", jQuery("#disk2").val());
        localStorage.setItem("minSize", jQuery("#minSize").val());
        console.log(localStorage.getItem("type"));
        console.log(localStorage.getItem("datacenter"));
        console.log(localStorage.getItem("cpu"));
        console.log(localStorage.getItem("ram"));
        console.log(localStorage.getItem("traffic"));
        console.log(localStorage.getItem("network"));
        console.log(localStorage.getItem("disk0"));
        console.log(localStorage.getItem("disk1"));
        console.log(localStorage.getItem("disk2"));
    }    
    else if(window.location.href.indexOf("/store/kametera") > -1) {
        localStorage.removeItem("type");
        localStorage.removeItem("datacenter");
        localStorage.removeItem("cpu");
        localStorage.removeItem("ram");
        localStorage.removeItem("traffic");
        localStorage.removeItem("network");
        localStorage.removeItem("serverid");
        localStorage.removeItem("disk0");
        localStorage.removeItem("disk1");
        localStorage.removeItem("disk2");
    }
    else if(window.location.href.indexOf("complete") > -1) {
        localStorage.removeItem("type");
        localStorage.removeItem("datacenter");
        localStorage.removeItem("cpu");
        localStorage.removeItem("ram");
        localStorage.removeItem("traffic");
        localStorage.removeItem("network");
        localStorage.removeItem("serverid");
        localStorage.removeItem("disk0");
        localStorage.removeItem("disk1");
        localStorage.removeItem("disk2");
    }

    if(window.location.href.indexOf("upgrade.php?type=configoptions") > -1){


        
        var configoptions = jQuery("[name^=configoption]");


        for (var i = 3 ; i < 6 ; i++)
        {
            jQuery(configoptions[i]).children().each(function() {
                this.setAttribute("hidden", true);
            });    
        }

        var current_ramtype = jQuery.trim(jQuery(configoptions[3]).parent().parent().prev().prev().text()).split(":");
        var current_datacenter = jQuery.trim(jQuery(configoptions[5]).parent().parent().prev().prev().text()).split(":");

        jQuery(configoptions[5]).children().each(function() {
            var vall = jQuery.trim(this.text);
            console.log(current_datacenter[0]);
            if (!vall.startsWith(current_datacenter[0] + ":") && !vall.includes("No Change"))
            {
                this.setAttribute("hidden", true);
            }
            else if (vall.includes("No Change"))
            {
                this.setAttribute("selected", true);
                this.removeAttribute("hidden");              
            }
            else
            {
                this.removeAttribute("hidden");
                this.removeAttribute("selected");
            }
        });
        jQuery(configoptions[3]).children().each(function() {
            var trimmed_val = jQuery.trim(this.text);
            var vall = trimmed_val.split(":");

            if (vall.length > 1)
            {
                if (jQuery.trim(vall[1].charAt(0)) != current_ramtype[1])
                {
                    this.setAttribute("hidden", true);
                }
                else
                {
                    this.removeAttribute("hidden");
                    this.removeAttribute("selected");
                }    
            }   
        });

        jQuery(configoptions[0]).on("change", function(){
            var dataCenter = jQuery(this).find(":selected").text();
            var datacenter = dataCenter.split(":");
            var trimmed_datacenter = jQuery.trim(datacenter[0]);
            jQuery(configoptions[5]).children().each(function() {
                var vall = jQuery.trim(this.text);
                jQuery(configoptions[5]).first().attr("selected", true);
                if (!vall.startsWith(trimmed_datacenter + ":") && !vall.includes("Select Bandwidth"))
                {
                    this.setAttribute("hidden", true);
                }
                else
                {
                    this.removeAttribute("hidden");
                    this.removeAttribute("selected");
                }
            });
    
        });
        jQuery(configoptions[1]).on("change", function(){
            var cpu = jQuery(this).find(":selected").text();
            var trimmed_cpu = jQuery.trim(cpu);
            var cpuarr = trimmed_cpu.split(" ");
            var cpu_type = cpuarr[0].charAt(cpuarr[0].length - 1);
            jQuery(configoptions[3]).children().each(function() {
                var trimmed_val = jQuery.trim(this.text);
                var vall = trimmed_val.split(":");
                console.log(vall[1])

                if (vall.length > 1)
                {
                    if (jQuery.trim(vall[1].charAt(0)) != cpu_type)
                    {
                        console.log(vall[1].charAt(0));
                        this.setAttribute("hidden", true);
                    }
                    else
                    {
                        this.removeAttribute("hidden");
                        this.removeAttribute("selected");
                    }    
                }
            });
    
        });    
        var minimum_required_pdisk = parseInt(jQuery.trim(jQuery(configoptions[4]).parent().parent().prev().prev().text().split("Minimum Required Disk ")[1].replace(/\D/g,'')));
        jQuery(configoptions[2]).children().each(function() {
            if (jQuery.trim(jQuery(this).text()) != "No Change")
            {
                if (parseInt(jQuery(this).text().split("GB")[0].replace(/\D/g,'')) < minimum_required_pdisk)
                {
                    this.setAttribute("hidden", true);
                }
            }
        });    
        console.log(jQuery(configoptions[7]).parent().parent().prev().prev().text());
        var minimum_required_disk1 = parseInt(jQuery.trim(jQuery(configoptions[7]).parent().parent().prev().prev().text().replace(/\D/g,'')));
        jQuery(configoptions[7]).children().each(function() {
            if (jQuery.trim(jQuery(this).text()) != "No Change")
            {
                if (parseInt(jQuery(this).text().split("GB")[0].replace(/\D/g,'')) < minimum_required_disk1 && parseInt(jQuery(this).text().split("GB")[0].replace(/\D/g,'')) != 0)
                {
                    this.setAttribute("hidden", true);
                }
            }
        });   
        var minimum_required_disk2 = parseInt(jQuery.trim(jQuery(configoptions[8]).parent().parent().prev().prev().text().replace(/\D/g,'')));
        jQuery(configoptions[8]).children().each(function() {
            if (jQuery.trim(jQuery(this).text()) != "No Change")
            {
                if (parseInt(jQuery(this).text().split("GB")[0].replace(/\D/g,'')) < minimum_required_disk2 && parseInt(jQuery(this).text().split("GB")[0].replace(/\D/g,'')) != 0)
                {
                    this.setAttribute("hidden", true);
                }
            }
        });
        if (parseInt(jQuery.trim(jQuery(configoptions[7]).parent().parent().prev().prev().text().replace(/\D/g,''))) == 0)
        {
            jQuery(configoptions[8]).children().each(function() {
                if (jQuery.trim(jQuery(this).text()) != "No Change")
                {
                    if (parseInt(jQuery(this).text().split("GB")[0].replace(/\D/g,'')) != 0)
                    {
                        this.setAttribute("hidden", true);
                    }
                }
            });
        }
    }

    if (localStorage.getItem("coperation") == "cpu")
    {
        for (var i = 0 ; i < 11 ; i++)
        {
            if (i == 1)
            {
                jQuery(configoptions[i]).parent().parent().parent().css("background-color", "rgba(0,0,0,.05)");
                continue;
            }
            else
            {
                jQuery(configoptions[i]).parent().parent().parent().hide();
            }
        }
    }
    else if (localStorage.getItem("coperation") == "ram")
    {
        for (var i = 0 ; i < 11 ; i++)
        {
            if (i == 3)
            {
                jQuery(configoptions[i]).parent().parent().parent().css("background-color", "rgba(0,0,0,.05)");
                continue;
            }
            else
            {
                jQuery(configoptions[i]).parent().parent().parent().hide();
            }
        }
    }
    else if (localStorage.getItem("coperation") == "pdisk")
    {
        for (var i = 0 ; i < 11 ; i++)
        {
            if (i == 2)
            {
                jQuery(configoptions[i]).parent().parent().parent().css("background-color", "rgba(0,0,0,.05)");
                continue;
            }
            else
            {
                jQuery(configoptions[i]).parent().parent().parent().hide();
            }
        }
    }
    else if (localStorage.getItem("coperation") == "disk1")
    {
        for (var i = 0 ; i < 11 ; i++)
        {
            if (i == 7)
            {
                jQuery(configoptions[i]).parent().parent().parent().css("background-color", "rgba(0,0,0,.05)");
                continue;
            }
            else
            {
                jQuery(configoptions[i]).parent().parent().parent().hide();
            }
        }
    }
    else if (localStorage.getItem("coperation") == "disk2")
    {
        for (var i = 0 ; i < 11 ; i++)
        {
            if (i == 8)
            {
                jQuery(configoptions[i]).parent().parent().parent().css("background-color", "rgba(0,0,0,.05)");
                continue;
            }
            else
            {
                jQuery(configoptions[i]).parent().parent().parent().hide();
            }
        }
    }
    else if (localStorage.getItem("coperation") == "ss")
    {
        for (var i = 0 ; i < 11 ; i++)
        {
            if (i == 9)
            {
                jQuery(configoptions[i]).parent().parent().parent().css("background-color", "rgba(0,0,0,.05)");
                continue;
            }
            else
            {
                jQuery(configoptions[i]).parent().parent().parent().hide();
            }
        }
    }
    else if (localStorage.getItem("coperation") == "backup")
    {
        for (var i = 0 ; i < 11 ; i++)
        {
            if (i == 10)
            {
                jQuery(configoptions[i]).parent().parent().parent().css("background-color", "rgba(0,0,0,.05)");
                continue;
            }
            else
            {
                jQuery(configoptions[i]).parent().parent().parent().hide();
            }
        }
    }
});