var object_image = false;
function saveImage(t, id_hidden, id_counter_tpe)
{    
    value = t.getAttribute("type-counter");  
    document.getElementById(id_hidden).value = 0;
    if (value == 'hidden') {
        document.getElementById(id_hidden).value = 2; 
        value = 1;
    } else if(value == 'color') {
        value = 0;
    }      
    t.className = "img-counter-active";
    if (object_image && object_image.getAttribute("type-counter") != value || document.getElementById(id_hidden).value == 2) {
        object_image.className = "img-counter";  
    }
    document.getElementById(id_counter_tpe).value = value;
    object_image = t;
}
function set_default_image(id, class_name)
{
    if (document.getElementById(id) != null) {
        document.getElementById(id).className = class_name;
        object_image = document.getElementById(id);
    }
}

function setColorPicker(id, default_value, id_send_color_image, id_counter_tpe, id_div_parent, id_hidden_value) 
{
    if (default_value == undefined || default_value == "") {
        default_value = "#fff";
    }
    jQuery('#'+id).minicolors({control:"wheel", position:"bottom right", defaultValue : default_value, 
        hide: function() {
            jQuery("#"+id_counter_tpe).val(0);
            jQuery("#"+id_hidden_value).val(0);
            jQuery("#"+id_div_parent).attr('class', 'img-counter-active');
            jQuery("#"+id_send_color_image).val(this.value);
        }, 
        change: function() {
            jQuery("#"+id_counter_tpe).val(0);
            jQuery("#"+id_hidden_value).val(0);
            jQuery("#"+id_div_parent).attr('class', 'img-counter-active');
            jQuery("#"+id_send_color_image).val(this.value); 
        }
    });
} 

function in_array(what, where) {
    var tmp;
    for(var i=0; i < where.length; i++) {
        tmp = where[i].split('=');
        if(tmp[0] == what) {
            return i;
        }
    }
    return false;
}

last_id = "";
function openInfo(id)
{
    if (last_id != "") {
        document.getElementById(last_id).style.display = "none";
    }
    document.getElementById(id).style.display = "";
    last_id = id;
}

