// Showing copy button on hover
jQuery(document).ready(function() {
    jQuery(".hover").mouseenter(function() {
        jQuery(this).children("button").css("visibility", "visible");
    }).mouseleave(function() {
        jQuery(this).children("button").css("visibility", "hidden");
    });
});

//Shortcode copy to clipboard
function copyToClipboard(element) {
    var $temp = jQuery("<input>");
    jQuery("body").append($temp);
    $temp.val(jQuery(element).siblings("span").text()).select();
    document.execCommand("copy");
    $temp.remove();
}