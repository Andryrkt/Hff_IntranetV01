<?php
function inputFields($placeholder, $name,$Id,$value, $type)
{
    $ele = "
    
        <div class= \"col\">
        <input type = '$type' name = '$name' id ='$Id' placeholer = '$placeholder' value='$value'
         class=\"form-control\"  autocomplete=\"off\" >
        </div>
    
    ";
    echo $ele;
};