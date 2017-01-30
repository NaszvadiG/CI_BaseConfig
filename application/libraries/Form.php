<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Form {

	private $title='Form Tanpa Judul';
	private $action='';
	private $image=FALSE;
	private $caption='Simpan';
	private $upload=false;
	private $_form=array();
	private $field=array();
	private $csrf=false;
	
	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->lang->load('label');
		$this->CI->load->library(['session','form']);
		$this->CI->load->helper(['text','form']);
	}

	function _get_csrf_nonce()
	{
		if($this->CI->session->userdata('csrfkey'))
		{
			$this->CI->session->unset_userdata('csrfkey');
			$this->CI->session->unset_userdata('csrfvalue');
		}
		
		$this->CI->load->helper('string');
		$output['key']   = random_string('alnum', 8);
		$output['value'] = random_string('alnum', 20);
		$this->CI->session->set_userdata('csrfkey', $output['key']);
		$this->CI->session->set_userdata('csrfvalue', $output['value']);
		return $output;
	}
	public function set_csrf()
	{
		$this->csrf=true;
	}


	public function set_upload()
	{
		$this->upload=true;
	} 
	
	function set_title($param)
	{
		$this->title=$param;
	}

	function set_picture($param)
	{
		$this->image=$param;
	}

	function set_action($param)
	{
		$this->action=$param;
	}

	function add_field($name='',$type='text',$value='',$option='',$readonly=false,$extra=false,$label=false)
	{
		$data=array(
				'name'=>$name,
				'type'=>$type,
				'title'=> $label,
				'value'=>$value,
				'id' =>$name,
				'class'=>'form-control',
				'placeholder'=>$label,
		);
		if($extra)
		{
			$data['class'].=' on-change';
		}
		if($type=='textarea')
		{
			$extra='id="editor"';
		}

		if($readonly)
		{
			$data['readonly']='readonly';
		}
		$output='<div class="form-group has-feedback">';
        if($label)
		{
			$output.='<div class="col-sm-4 control-label no-padding-right">'.lang($data['title']).'</div>';
			$output.='<div class="col-sm-8">';
        }
        else
        {
        	$output.='<div class="col-sm-12">';
        }
        switch($type)
		{
			case 'label' :  $output.='<p>: '.$value.'</p>';break;
			case 'select' : $output.=form_dropdown($name,$option,$value,array('class'=>'form-control'));break; 
          	case 'textarea' : $output.=form_textarea($name,$value==''?'':$value,$extra);
          		break;
          	default : $output.=form_input($data,$data['value'],$extra);
        }
        $output.='</div></div>';
        $this->field[]= $output;
	}

	function open($option=false)
	{
		if($this->upload)
		{
			$action='http://'.$_SERVER['HTTP_HOST'].'/'.$this->action;
			$output='<form action="'.$action.'" target="uploadFrame" class="form-horizontal" role="form" id="upload" enctype="multipart/form-data" method="post" accept-charset="utf-8">';
    	}
    	else
    	{
    		$output=form_open($this->action,'class="form-horizontal" role="form" id="formmodal"');
    	}
    	$output.='<div class="box box-header box-info" style="height:60px;margin-bottom:0px;padding-top:10px;background-color:#fff">';
    	$output.='<a href="#" accesskey="c" class="btn btn-default cls-ajax pull-left" title="Close">';
    	$output.='<i class="fa fa-close"></i><span class="icon-label"><u>C</u>lose';
    	$output.='</span></a>';
    	if($this->upload==false)
     	{
     		$output.='<a href="#" accesskey="s" id="AjaxSave" data-href="'.$this->action.'" class="btn btn-load btn-default btn-ajax-save pull-right" style="right:5px" title="Save">';
     		$output.='<i class="fa fa-floppy-o"></i><span class="icon-label"> <u>S</u>ave</span>'; 
        	$output.='</a>';	
			if($option==true)
     		{
     	       	//$action=explode('/',$this->action);
     	       	//$action[2] =  'new';

     	       	$output.='<a href="#" accesskey="a" id="AjaxSaveAsNew"data-href="'.$this->action.'" class="btn btn-load btn-default btn-ajax-save pull-right" style="right:5px" title="Save as New">';
     	    	$output.='<i class="fa fa-floppy-o"></i><span class="icon-label">Save <u>A</u>s New'; 
        		$output.='</span></a>';	
        	}
     	}
     	else
     	{
     		$output.='<button id="uploadBtn" class="btn btn-default btn-ajax-upload pull-right" ><i class="fa fa-floppy-o"></i> <u>U</u>pload</button>';
     	}
    	$output.='<div class="box-title" style="padding-bottom:10px;">';
    	$output.='<h4 class="modal-title" id="dialog-title"> &nbsp;';
    	$output.='<b id="title-bar">&nbsp;'.word_limiter($this->title,4).'&nbsp</b></h4>';
    	$output.='</div>';
    	$output.='</div>';
    	$output.='<div class="box box-body" style="margin-bottom:0px;padding-bottom:0px;border-top:2px solid #ddd;">';
    	return $output;
	}

	function close($option)
	{
		$csrf=$this->_get_csrf_nonce();
		$data = array(
        	'type'  => 'hidden',
        	'name'  => $csrf['key'],
        	'id'    => 'csrf',
        	'value' => $csrf['value'],
        );
        $close = array(
        	'type'  => 'hidden',
        	'name'  => 'close',
        	'id'    => 'closeForm',
        	'value' => 'false',
        );
        $saveAs = array(
        	'type'  => 'hidden',
        	'id'	=>'saveOption',
        	'name'  => 'saveAs',
        	'value'	=>'',
  		);
    	$output='</div>';
     	//$output.='<div class="box box-footer" style="height:36px;margin-bottom:2px;border-top:2px solid #ddd;">';
     	//$output.='<label>[F2]: Load Form, [F3],[ESC]: Close form</label>'; 
     	//$output.='</div>';
     	$output.=form_input($data);
     	if($option==true)
     	{
     		$output.=form_input($saveAs);
     	}
     	$output.=form_input($close);
     	$output.=form_close();
     	return $output;
	}

	public function render($option=false)
	{
		$output['output']=$this->open($option);
		if($this->image){
			$output['output'].='<div class="col-sm-4">';
			$output['output'].='<img src="'.$this->image['src'].'" id="'.$this->image['id'].'" style="'.$this->image['style'].'"/>';
			$output['output'].='</div>';
		} 
		if(count($this->field)>0)
		{
			foreach($this->field as $key)
			{
				$output['output'].=$key;	
			}
		}
		$output['output'].=$this->close($option);
		$this->CI->load->view('html',$output);
	}
}