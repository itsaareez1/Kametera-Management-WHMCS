/* *****************************************************

Project: Horn - WHMCS Dashboard Hosting Theme
Description: Horn Responsive Premium Theme Designed for all web hosting providers
Author: inebur (Rúben Rodrigues)
Author URI: http://inebur.com/
Author Envato: https://themeforest.net/user/inebur
Copyright: 2021 inebur
Version: 1.7

[Main Javascript]
***************************************************** */
document.addEventListener('DOMContentLoaded', function() {
    "use strict";
});
/*-- SVG Change color style --*/
$("img.svg")
.each(function() {
    var $img = jQuery(this);
    var attributes = $img.prop("attributes");
    var imgURL = $img.attr("src");
    $.get(imgURL, function(data) {
        var $svg = $(data)
        .find('svg');
        $svg = $svg.removeAttr('xmlns:a');
        $.each(attributes, function() {
            $svg.attr(this.name, this.value);
        });
        $img.replaceWith($svg);
    });
});

jQuery('<div class="quantity-nav"><div class="quantity-button quantity-up">+</div><div class="quantity-button quantity-down">-</div></div>').insertAfter('.quantity input');
jQuery('.quantity').each(function() {
  var spinner = jQuery(this),
    input = spinner.find('input[type="number"]'),
    btnUp = spinner.find('.quantity-up'),
    btnDown = spinner.find('.quantity-down'),
    min = input.attr('min'),
    max = input.attr('max');

  btnUp.click(function() {
    var oldValue = parseFloat(input.val());
    if (oldValue >= max) {
      var newVal = oldValue;
    } else {
      var newVal = oldValue + 1;
    }
    spinner.find("input").val(newVal);
    spinner.find("input").trigger("change");
  });

  btnDown.click(function() {
    var oldValue = parseFloat(input.val());
    if (oldValue <= min) {
      var newVal = oldValue;
    } else {
      var newVal = oldValue - 1;
    }
    spinner.find("input").val(newVal);
    spinner.find("input").trigger("change");
  });

});

