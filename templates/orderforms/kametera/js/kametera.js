jQuery(document).ready(function(){
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
    if(window.location.href.indexOf("confproduct") > -1) {
        
    }

    var configoptions = jQuery("[name^=configoption]");
    var customfields = jQuery("[name^=customfield]");
    jQuery(configoptions[0]).prepend('<option selected hidden>Select Region</option>');
    jQuery(configoptions[1]).prepend('<option selected hidden>Select CPU Type</option>');
    jQuery(configoptions[2]).prepend('<option selected hidden>Select Disk Size</option>');
    jQuery(configoptions[4]).prepend('<option selected hidden>Select OS</option>');
    jQuery(configoptions[5]).prepend('<option selected hidden>Select Bandwidth</option>');
    jQuery(configoptions[3]).prepend('<option selected hidden>Select RAM (MBs)</option>');


    for (var i = 3 ; i < 6 ; i++)
    {
        jQuery(configoptions[i]).children().each(function() {
            this.setAttribute("hidden", true);
        });    
    }
    jQuery(configoptions[0]).on("change", function(){
        var dataCenter = jQuery(this).find(":selected").text();
        var datacenter = dataCenter.split(":");
        var trimmed_datacenter = jQuery.trim(datacenter[0]);
        if (localStorage.getItem("type") != "clone"){
            jQuery(configoptions[4]).children().each(function() {
                var vall = jQuery.trim(this.text);
                
                jQuery(configoptions[4]).first().attr("selected", true);
                if (!vall.startsWith(trimmed_datacenter + ":") && !vall.includes("Select OS"))
                {
                    this.setAttribute("hidden", true);
                }
                else
                {
                    this.removeAttribute("hidden");
                    this.removeAttribute("selected");
                }
            });
            jQuery("#" + jQuery(configoptions[4]).attr("id") + " option").filter(function() {
                return jQuery.trim(jQuery(this).text()) == "Select OS";
            }).prop("selected", true); 
            jQuery("#" + jQuery(configoptions[5]).attr("id") + " option").filter(function() {
                return jQuery.trim(jQuery(this).text()) == "Select Bandwidth";
            }).prop("selected", true); 
    
        }
        else
        {
            if(jQuery("#" + jQuery(configoptions[5]).attr("id") + " option:contains('Select Bandwidth')").length < 1)
            {
                jQuery(configoptions[5]).prepend('<option selected hidden>Select Bandwidth</option>');
            }
        }        
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
        var cpu_type = trimmed_cpu.charAt(trimmed_cpu.length - 1);

        jQuery(configoptions[3]).children().each(function() {
            var vall = jQuery.trim(this.text);
            jQuery(configoptions[3]).first().attr("selected", true);
            if (vall.charAt(vall.length - 1) != cpu_type && !vall.includes("Select RAM (MBs)"))
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
    jQuery(configoptions[4]).on("change", function(){
        
        jQuery(configoptions[2]).children().each(function() {
            this.removeAttribute("hidden");
        });
        var image = jQuery(this).find(":selected").text();
        var diskSize = jQuery(configoptions[2]).find(":selected").text();
        if (diskSize == "")
        {
            diskSize = 0;
        }
        var recommended_size = image.split("Minimum Required Disk ");
        var size = recommended_size[1];

        if (parseInt(jQuery.trim(recommended_size[1]).replace(/\D/g,'')) >= parseInt(jQuery.trim(diskSize.replace(/\D/g,'')))){
            jQuery("#" + jQuery(configoptions[2]).attr("id") + " option").filter(function() {
                return jQuery.trim(jQuery(this).text()) == jQuery.trim(size);
            }).prop("selected", true);    
        }
        jQuery(configoptions[2]).children().each(function() {
            if (parseInt(jQuery.trim(jQuery(this).text()).replace(/\D/g,'')) >= parseInt(jQuery.trim(size.replace(/\D/g,''))))
            {
                return false;
            }
            else
            {
                this.setAttribute("hidden", true);
            }
        });
    });
    
    jQuery("#fullpage-overlay").remove();
    console.log(localStorage.getItem("type"));
    if (localStorage.getItem("type") == "clone")
    {
        if(window.location.href.indexOf("confproduct") > -1) {
            jQuery(configoptions[0]).find('option').get(0).remove();
            jQuery(configoptions[1]).find('option').get(0).remove();
            jQuery(configoptions[2]).find('option').get(0).remove();
            jQuery(configoptions[3]).find('option').get(0).remove();
            jQuery(configoptions[4]).find('option').get(0).remove();
            jQuery(configoptions[5]).find('option').get(0).remove(); 
        }
        
        jQuery(customfields[0]).val(localStorage.getItem("serverid"));
        jQuery(customfields[0]).attr('readonly', true);


        console.log(localStorage.getItem("datacenter"));
        console.log(localStorage.getItem("cpu"));
        console.log(localStorage.getItem("ram"));
        console.log(localStorage.getItem("traffic"));
        console.log(localStorage.getItem("serverid"));
        if (localStorage.getItem("datacenter") !== null)
        {
            jQuery("#" + jQuery(configoptions[0]).attr("id") + " option:contains('" + localStorage.getItem("datacenter") + "')").prop("selected","selected");
            jQuery(configoptions[0]).children().each(function() {
                var vall = jQuery.trim(this.text);
                if (jQuery.trim(jQuery(this).text()) != localStorage.getItem("datacenter"))
                {
                    this.setAttribute("hidden", true);
                    this.removeAttribute("selected");
                }
            });
        }
        if (localStorage.getItem("cpu") !== null)
        {
            //jQuery("#" + jQuery(configoptions[1]).attr("id") + " option:matches('" + localStorage.getItem("cpu") + "')").prop("selected","selected");
            jQuery("#" + jQuery(configoptions[1]).attr("id") + " option").filter(function() {
                return jQuery.trim(jQuery(this).text()) == localStorage.getItem("cpu");
            }).prop("selected", true);

            // jQuery("#" + jQuery(configoptions[1]).attr("id")).find('option[text="'+ localStorage.getItem("cpu") +'"]').prop("selected","selected");    
        }
        if (localStorage.getItem("ram") !== null)
        {
            //jQuery("#" + jQuery(configoptions[1]).attr("id") + " option:matches('" + localStorage.getItem("cpu") + "')").prop("selected","selected");
            jQuery("#" + jQuery(configoptions[3]).attr("id") + " option").filter(function() {
                var ram = localStorage.getItem("ram").replace(/\D/g,'') + "MB:" + localStorage.getItem("ram").charAt(0);
                return jQuery.trim(jQuery(this).text()) == ram;
            }).prop("selected", true);

            // jQuery("#" + jQuery(configoptions[1]).attr("id")).find('option[text="'+ localStorage.getItem("cpu") +'"]').prop("selected","selected");    
        }

        if (localStorage.getItem("network") !== null)
        {
            jQuery("#" + jQuery(configoptions[3]).attr("id") + " option:contains('" + localStorage.getItem("network") + "')").prop("selected","selected");                            
        }
        var dataCenter = jQuery(configoptions[0]).find(":selected").text();
        var datacenter = dataCenter.split(":");
        var trimmed_datacenter = jQuery.trim(datacenter[0]);
        
        jQuery("#" + jQuery(configoptions[4]).attr("id") + " option:first").text("Same as previous");            
        jQuery(configoptions[4]).children().each(function() {
            var vall = jQuery.trim(this.text);
            jQuery(configoptions[4]).first().attr("selected", true);
            if (vall.includes("Same as previous"))
            {
                this.removeAttribute("hidden");
                this.setAttribute("selected", true);
            }
            else
            {
                this.setAttribute("hidden", true);
                this.removeAttribute("selected");
            }
        });
        // jQuery("#" + jQuery(configoptions[2]).attr("id") + " option:first").text("Same as previous");            
        // jQuery(configoptions[2]).children().each(function() {
        //     var vall = jQuery.trim(this.text);
        //     jQuery(configoptions[2]).first().attr("selected", true);
        //     if (vall.includes("Same as previous"))
        //     {
        //         this.removeAttribute("hidden");
        //         this.setAttribute("selected", true);
        //     }
        //     else
        //     {
        //         this.setAttribute("hidden", true);
        //         this.removeAttribute("selected");
        //     }
        // });
        jQuery("#" + jQuery(configoptions[2]).attr("id") + " option").filter(function() {
            return jQuery.trim(jQuery(this).text()) == localStorage.getItem("disk0");
        }).prop("selected", true);
        jQuery(configoptions[2]).children().each(function(){
            if (jQuery.trim(jQuery(this).text()) == localStorage.getItem("disk0"))
            {
                return false;
            }
            else
            {
                this.setAttribute("hidden", true);
            }

        });
        jQuery("#" + jQuery(configoptions[7]).attr("id") + " option").filter(function() {
            return jQuery.trim(jQuery(this).text()) == localStorage.getItem("disk1");
        }).prop("selected", true);  
        jQuery("#" + jQuery(configoptions[8]).attr("id") + " option").filter(function() {
            return jQuery.trim(jQuery(this).text()) == localStorage.getItem("disk2");
        }).prop("selected", true);                  
        jQuery(configoptions[5]).children().each(function() {
            var vall = jQuery.trim(this.text);
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
        if (localStorage.getItem("traffic") !== null)
        {
            jQuery("#" + jQuery(configoptions[5]).attr("id") + " option").filter(function() {
                var traffic = jQuery(this).text().split(":");
                return jQuery.trim(traffic[0]) == trimmed_datacenter && jQuery.trim(traffic[2]) == jQuery.trim(localStorage.getItem("traffic"));
            }).prop("selected", true);
        }
    }
    else
    {
        console.log(customfields.length);
        if (customfields.length > 1)
        {
            jQuery(customfields[0]).val("");
            jQuery(customfields[0]).hide();  
            jQuery(customfields[0]).prev().hide();              
        }
        else
        {
            jQuery(customfields[0]).val("");
            jQuery(customfields[0]).parent().parent().hide();    
            jQuery(customfields[0]).parent().parent().prev().hide();
        }
    }

    if(typeof window.langPasswordWeak === 'undefined'){
        window.langPasswordWeak = "Weak";
    }
    if(typeof window.langPasswordModerate === 'undefined'){
        window.langPasswordModerate = "Moderate";
    }
    if(typeof window.langPasswordStrong === 'undefined'){
        window.langPasswordStrong = "Strong";
    }

    jQuery("#inputRootpw").attr("data-error-threshold", 50);
    jQuery("#inputRootpw").attr("data-warning-threshold", 75);


    jQuery("#inputRootpw").parent().parent().append('<div class="password-strength-meter">'
    + '<div class="progress">'
    + '<div class="progress-bar bg-success bg-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="passwordStrengthMeterBar">'
    + '</div>'
    + '</div>'
    + '<p class="text-center small text-muted" id="passwordStrengthTextLabel"></p>');

    jQuery("#inputHostname").parent().parent().append('<button type="button" id="generateP" class="btn btn-default btn-sm btn-sm-block generate-password" data-targetfields="inputRootpw,">Generate Password</button>');

    jQuery("#generateP").on("click", function(){
        jQuery("body").append('<div class="modal-backdrop fade show"></div>');
        jQuery("body").addClass("modal-open");
        jQuery("#modalGeneratePassword").addClass("fade show");
        jQuery("#modalGeneratePassword").css("display", "block");
        jQuery("#modalGeneratePassword").attr("aria-modal", "true");
        jQuery("#modalGeneratePassword").attr("role", "dialog");
        
    })
    console.log(jQuery("#modalGeneratePassword").children().children().children().last().children().first());
    jQuery("#modalGeneratePassword").children().children().children().last().children().first().on('click', function(){
        jQuery("body").append('<div class="modal-backdrop fade show"></div>');
        jQuery("body").removeClass("modal-open");
        jQuery(".modal-backdrop").remove();
        jQuery("#modalGeneratePassword").removeClass("fade show");
        jQuery("#modalGeneratePassword").css("display", "");
        jQuery("#modalGeneratePassword").removeAttr("aria-modal", "true");
        jQuery("#modalGeneratePassword").removeAttr("role", "dialog");

    })
    if(window.location.href.indexOf("confproduct") > -1) {
        jQuery('#btnGeneratePasswordInsert')
        .click(WHMCS.ui.clipboard.copy)
        .click(function(e) {
            jQuery(this).closest('.modal').modal('hide');
                var generatedPassword = jQuery('#inputGeneratePasswordOutput');
                jQuery('#inputRootpw').val(generatedPassword.val())
                .trigger('keyup');
            // Remove the generated password.
            generatedPassword.val('');
            jQuery("body").append('<div class="modal-backdrop fade show"></div>');
            jQuery("body").removeClass("modal-open");
            jQuery(".modal-backdrop").remove();
            jQuery("#modalGeneratePassword").removeClass("fade show");
            jQuery("#modalGeneratePassword").css("display", "");
            jQuery("#modalGeneratePassword").removeAttr("aria-modal", "true");
            jQuery("#modalGeneratePassword").removeAttr("role", "dialog");
        });            
    }
    // jQuery("#inputRootpw").keyup(function () {
    //     var pwvalue = jQuery("#inputRootpw").val();
    //     var pwstrength = getPasswordStrength(pwvalue);
    //     jQuery("#pwstrength").html(langPasswordStrong);
    //     jQuery("#pwstrengthpos").css("background-color","#33CC00");

    //     var errorThreshold = !isNaN(parseInt(jQuery(this).data('error-threshold'))) ? jQuery(this).data('error-threshold') : 50;
    //     var warningThreshold = !isNaN(parseInt(jQuery(this).data('warning-threshold'))) ? jQuery(this).data('warning-threshold') : 75;

    //     if (pwstrength<warningThreshold) {
    //         jQuery("#pwstrength").html(langPasswordModerate);
    //         jQuery("#pwstrengthpos").css("background-color","#ff6600");
    //     }
    //     if (pwstrength<errorThreshold) {
    //         jQuery("#pwstrength").html(langPasswordWeak);
    //         jQuery("#pwstrengthpos").css("background-color","#cc0000");
    //     }
    //     jQuery("#pwstrengthpos").css("width",pwstrength);
    //     jQuery("#pwstrengthneg").css("width",100-pwstrength);
    // });
    
})

function registerFormPasswordStrengthFeedback()
{
    passwordStrength = getPasswordStrength(jQuery(this).val());
    if (passwordStrength < 85)
    {
        jQuery("#btnCompleteProductConfig").prop('disabled', true);
    }
    else
    {
        jQuery("#btnCompleteProductConfig").prop('disabled', false);
    }

    var errorThreshold = !isNaN(parseInt(jQuery(this).data('error-threshold'))) ? jQuery(this).data('error-threshold') : 50;
    var warningThreshold = !isNaN(parseInt(jQuery(this).data('warning-threshold'))) ? jQuery(this).data('warning-threshold') : 75;

    if (passwordStrength >= warningThreshold) {
        textLabel = langPasswordStrong;
        cssClass = 'success';
    } else if (passwordStrength >= errorThreshold) {
        textLabel = langPasswordModerate;
        cssClass = 'warning';
    } else {
        textLabel = langPasswordWeak;
        cssClass = 'danger';
    }
    jQuery("#passwordStrengthTextLabel").html(langPasswordStrength + ': ' + passwordStrength + '% ' + textLabel);
    jQuery("#passwordStrengthMeterBar").css('width', passwordStrength + '%').attr('aria-valuenow', passwordStrength);
    // var ver = parseInt(jQuery.fn.tooltip.Constructor.VERSION);
    jQuery("#passwordStrengthMeterBar").removeClass('bg-danger bg-warning bg-success').addClass('bg-' + cssClass);

    // switch (ver) {
    //     case 3:
    //         jQuery("#passwordStrengthMeterBar").removeClass('progress-bar-success progress-bar-warning progress-bar-danger').addClass('progress-bar-' + cssClass);
    //         break;
    //     default:
    //         jQuery("#passwordStrengthMeterBar").removeClass('bg-danger bg-warning bg-success').addClass('bg-' + cssClass);
    //         break;
    // }
}

function getPasswordStrength(pw){
    var pwlength=(pw.length);
    if(pwlength>5)pwlength=5;
    var numnumeric=pw.replace(/[0-9]/g,"");
    var numeric=(pw.length-numnumeric.length);
    if(numeric>3)numeric=3;
    var symbols=pw.replace(/\W/g,"");
    var numsymbols=(pw.length-symbols.length);
    if(numsymbols>3)numsymbols=3;
    var numupper=pw.replace(/[A-Z]/g,"");
    var upper=(pw.length-numupper.length);
    if(upper>3)upper=3;
    var pwstrength=((pwlength*10)-20)+(numeric*10)+(numsymbols*15)+(upper*10);
    if(pwstrength<0){pwstrength=0}
    if(pwstrength>100){pwstrength=100}
    return pwstrength;
}

function showStrengthBar() {
    if(typeof window.langPasswordStrength === 'undefined'){
        window.langPasswordStrength = "Password Strength";
    }
    if(typeof window.langPasswordWeak === 'undefined'){
        window.langPasswordWeak = "Weak";
    }
    document.write('<table align="center" style="width:auto;"><tr><td>'+langPasswordStrength+':</td><td><div id="pwstrengthpos" style="position:relative;float:left;width:0px;background-color:#33CC00;border:1px solid #000;border-right:0px;">&nbsp;</div><div id="pwstrengthneg" style="position:relative;float:left;width:100px;background-color:#efefef;border:1px solid #000;border-left:0px;">&nbsp;</div></td><td><div id="pwstrength">'+langPasswordWeak+'</div></td></tr></table>');
}
