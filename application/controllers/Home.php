<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Home extends Panel_Controller {

	function index() 
	{
		$output = [];
		$this->load->view('template',$output);
	}	

	function sidebar()
	{
		$menus=[];
		if($this->session->login) 
		{			
			$roles= explode('|',$this->session->roles);
			foreach($roles as $role)
			{
				if(in_array($role,['teacher','student','candidate']))
				{
					$this->session->set_userdata('user_type',$role);
					$data['user_type']=$role;	
				}

				$this->config->load($role);
				$dataMenu[$role] = $this->config->item($role);
				if($role =='student')
				{
					$leader = $this->rooms->leader($this->session->user_id);
					if($leader != null)
					{	
						$this->session->set_userdata('leader',true);
						$this->config->load('leader');
						$dataMenu['leader'] = $this->config->item('leader');
					}
					else
					{
						$this->session->set_userdata('leader',false);
					}
				}

				if($role == 'teacher')
				{
					if(is_array($this->session->jobs))
					{
						foreach($this->session->jobs as $job)
						{
							$this->config->load($job);
							$dataMenu[$job] = $this->config->item($job);
						}					
						if($this->mentors->checkExists($this->session->user_id))
						{
							$this->config->load('mentor');
							$dataMenu['mentor'] = $this->config->item('mentor');
							$this->session->set_userdata('mentor',true);
						}
						else
						{
							$this->session->set_userdata('mentor',false);
						}
					}
				}
			}
		}
		if(isset($dataMenu))
		{
			$this->load->view('sidebar',['data'=>$dataMenu]);			
		}
	}

	function menubar()
	{
		if($this->session->login) 
		{
			$this->load->model(['users','configs']);
			$user = $this->users->getByUserId($this->session->user_id);
			$name=explode(' ',$user->original_name);
			$data=array(
		   		'name_user'=>$name[0],
		   		'pict_profile'=>$user->pict_profile
			);
			$output['output']=data_menubar($data);
			$this->load->view('html',$output);
		
		
			foreach($this->configs->getAll() as $val)
			{
				$this->session->set_userdata($val->name,$val->content);
			}
		}
	}

	function panelAuth()
	{
		if(! $this->session->login) 
		{
			$this->load->library('recaptcha');
			$data['csrf'] = $this->form->_get_csrf_nonce();
			$data['script_captcha'] = $this->recaptcha->getScriptTag();
			$this->load->view('home_nosession',$data);
		}
	}
}
