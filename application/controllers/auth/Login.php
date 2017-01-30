<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends Nosession_Controller {
	
	function index()
	{		
		$this->form_validation->set_rules('identity','lang:identity','trim|required');
		$this->form_validation->set_rules('password_log','lang:password','trim|required');
		if($this->post->_valid_csrf_nonce() === FALSE)
		{
			$this->json($this->post->error_csrf());
		}
		else
		{
			if(valid_email($this->input->post('identity')))
			{
				$auth = $this->users->getByEmail($this->input->post('identity'));
				$identity = 'email';
			}
			else
			{
				$auth = $this->users->getByUserID($this->input->post('identity'));
				$identity = 'userID';
			}
			//$response = $this->recaptcha->verifyResponse($this->input->post('g-recaptcha-response'));
			if($this->form_validation->run()===true)// && $response['success']==true)
			{
				if(password_verify($this->input->post('password_log'), $auth->password))
				{
					$user = [
						'user_id' => $auth->id,
						'roles' => $auth->roles,
						'identityValue' => $this->input->post('identity'),
						'identity' => $identity,
						'login' => true,
					];
					$user['jobs'] = $auth->jobs != null ? explode(';',$auth->jobs) : false;
					$this->session->set_userdata($user);
					$_COOKIE['roxy']=$auth->id;
					$this->json($this->post->success_auth('login'));
				}
				else
				{
					$user = [
						'login' => false,
					];
					$this->session->set_userdata($user);
					$this->json($this->post->fail_auth('Identitas dan password tidak sesuai'));	
				}
			}
			else
			{
				$this->json($this->post->error_validation());
			}
		}
	}
		
}
