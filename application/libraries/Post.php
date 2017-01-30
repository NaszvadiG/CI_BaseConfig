<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Post {



	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->lang->load('label');
		$this->CI->load->library('session');
		$this->CI->load->helper('security');
	}

	function parser($post)
	{
		$data=array();
		$pattern_body="/<tbody([^`]*?)\/tbody>/";
		preg_match_all($pattern_body,$post['table_user'],$body);
		if(count($body[0])==0)
		{
			$output=array('status'=>false,'message'=>'Belum ada tabel');
		}
		else
		{
			$pattern_row="/<tr([^`]*?)\/tr>/";
			preg_match_all($pattern_row,implode('',$body[0]),$rows);
			if(count($rows[0])==0)
			{
				$output=array('status'=>false,'message'=>'Belum ada baris yang ditambahkan');
			}
			else
			{
				foreach($rows[0] as $key=>$row)
				{
					$pattern_col="/<td([^`]*?)\/td>/";
					preg_match_all($pattern_col,$row,$col);
					if(count($col[0])>= $post['col_username'] && count($col[0])>= $post['col_name'] && count($col[0])>= $post['col_gender'] && count($col[0])>= $post['col_password'] )
					{
						$field['username']=trim(strip_tags($col[0][$post['col_username']-1])," \t\n\r\0\x0B");
						$field['original_name']=trim(str_replace('&nbsp;',' ',strip_tags($col[0][$post['col_name']-1]))," \t\n\r\0\x0B");
						$name=explode(' ',$field['original_name']);
						$field['first_name']=$name[0];
						$field['last_name']=$name[count($name)-1];
						$gender=strtoupper(trim(str_replace('&nbsp;',' ',strip_tags($col[0][$post['col_gender']-1]))," \t\n\r\0\x0B"));
						$field['gender']= $gender=='L'?1:($gender=='P'?2:0);
						$field['password']=trim(str_replace('&nbsp;',' ',strip_tags($col[0][$post['col_password']-1]))," \t\n\r\0\x0B");
						array_push($data,$field);
					}
					else{
						$output=array('status'=>false,'message'=>'Kolom tabel tidak sesuai');
					}
				}
			}
			if(count($data)>0)
			{
				$output=array('status'=>true,'data'=>$data);
			}
			else
			{
				$output=array('status'=>false,'message'=>'Kolom tabel tidak sesuai');
			}
		}
		return $output;
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

	function _valid_csrf_nonce()
	{
		if ($this->CI->input->post($this->CI->session->userdata('csrfkey')) !== FALSE &&
			$this->CI->input->post($this->CI->session->userdata('csrfkey')) == $this->CI->session->userdata('csrfvalue'))
		{
			$this->CI->session->unset_userdata('csrfkey');
			$this->CI->session->unset_userdata('csrfvalue');
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function error_csrf()
	{
		$data['message'] = 'Kesalahan CSRF. Silahkan kirim ulang. <br>Jika gagal silahkan refresh browser anda';
		$data['atribut']='fail';
		$csrf=$this->_get_csrf_nonce();
		$data['key']=$csrf['key'];
		$data['value']=$csrf['value'];
		return $data;
	}

	function error_validation()
	{
		$csrf=$this->_get_csrf_nonce();
		$data['key']=$csrf['key'];
		$data['value']=$csrf['value'];
		$data['message'] = validation_errors();
		$data['atribut']='fail';
		return $data;
	}

	function success_update()
	{
		$data['status']='success';
		$csrf=$this->_get_csrf_nonce();
		$data['key']=$csrf['key'];
		$data['value']=$csrf['value'];
		$data['message']='Data berhasil diperbarui';
		return $data;
	}

	function success_insert($id)
	{
		$data['message']='Data berhasil ditambahkan';
		$csrf=$this->_get_csrf_nonce();
		$data['key']=$csrf['key'];
		$data['value']=$csrf['value'];
		$data['id'] = $id;
		return $data;
	}

	function success_delete()
	{
		$data['message']='Data berhasil dihapus';
		$data['atribut']='remove';
		return $data;
	}

	function success_mutation()
	{
		$data['message']='Data berhasil diproses';
		$data['atribut']='remove';
		return $data;
	}

	function success_import($class='dropdown-primary')
	{
		$data['message'] = 'Data berhasil diimport';
		$data['status'] = 'success';
		return $data;
	}

	function fail_import($message)
	{
		$csrf=$this->_get_csrf_nonce();
		$data['key']=$csrf['key'];
		$data['value']=$csrf['value'];
		$data['message'] = $message;
		$data['status'] = 'warning';
		return $data;
	}

	function error_duplicate($message)
	{
		$csrf=$this->_get_csrf_nonce();
		$data['key']=$csrf['key'];
		$data['value']=$csrf['value'];
		$data['message'] = $message;
		$data['status'] = 'warning';
		$data['atribut']='fail';
		return $data;
	}

	function success_insert_batch($class='dropdown-primary',$change='change',$close=false)
	{
		$data['message'] = 'Data berhasil ditambahkan';
		$data['atribut'] = 'insert';
		$csrf=$this->_get_csrf_nonce();
		$data['key']=$csrf['key'];
		$data['value']=$csrf['value'];
		return $data;
	}

	function success_remove_batch($class='dropdown-primary')
	{
		$data['message'] = "Data berhasil dihapus dari daftar";
		$data['atribut']='remove';
		return $data;
	}

	function success_upload($controller,$param)
	{
		$script="<script>";
		$script.="parent.$('.cls-ajax').click();parent.alert_notif('success','Berkas berhasil diupload');";
		$script.="parent.refresh=true; parent.refreshGrid()";
		$script.="</script>"; 
		echo $script;
	}

	function error_upload($message)
	{
		$data['message'] = 'Berkas berhasil diupload';
		$data['status'] = 'fail';
		$data['triger']='nothing';
		$script="<script>";
		$script.="parent.alert_notif('warning','".$message."');";
		$script.="</script>";
		echo $script;
	}
	
	
	function success($message='sukses')
	{
		$data['message'] = $message;
		$data['atribut']='nothing';
		$data['status']='success';
		return $data;
	}

	function success_auth($atribut)
	{
		$csrf=$this->_get_csrf_nonce();
		$data['key']=$csrf['key'];
		$data['value']=$csrf['value'];
		$data['atribut']=$atribut;
		return $data;
	}

	function fail_auth($message='')
	{
		$csrf=$this->_get_csrf_nonce();
		$data['key']=$csrf['key'];
		$data['value']=$csrf['value'];
		$data['message'] = $message;
		$data['atribut']='fail';
		return $data;
	}

	function fail($message='Data Gagal Diproses')
	{
		$csrf=$this->_get_csrf_nonce();
		$data['key']=$csrf['key'];
		$data['value']=$csrf['value'];
		$data['message'] = $message;
		$data['atribut']='fail';
		return $data;
	}
}