<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Operator extends Base_Controller {

	function __construct()
	{
		parent::__construct();
		if($this->session->login && in_array('operator',explode('|',$this->session->roles)))		
		{
			return true;
		}
		return false;
	}
}