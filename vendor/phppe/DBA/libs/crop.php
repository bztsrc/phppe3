<?php

function crop($str)
{
    return (strlen($str)<64)?$str:substr($str,0,64)." ...";
}
