<?php
/**
 * minimal view model for ORM
 */
namespace PHPPE;

class Views extends \PHPPE\Model
{
    public $ctrl = "";
    public $data = "";
    public $jslib = [];
    public $css = [];

    static $_table = "views";

}
