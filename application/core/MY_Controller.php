<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Panel_Controller extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model(['rooms','mentors']);
		$this->load->helper(['url','form','language','menu','bootstrap_theme','filter']);
		$this->load->library(['form','session']);
		$this->lang->load(['menu','icon']);
	}

}

class Base_Controller extends CI_Controller {

	protected $dir;
	public $data;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Jakarta');

		if (! $this->input->is_ajax_request() && ENVIRONMENT == 'production') {
   			exit('No direct script access allowed');
		}

		$this->load->library(['recaptcha','session','encryption']);
		$this->load->helper(['url','language','array','filter','string']);
		$this->lang->load(['label','icon']);
		$this->load->database();

		$url = explode('/',uri_string());
		
		$this->controller = $url[1];
	}

	/*
	Menampilkan page sesuai jenis page
	*/
	public function view()
	{
		$path = str_replace('_','/',$this->uri->segments[3]);
		$this->load->config($this->uri->segments[1].'/'.$path);
		$this->load->library('grid');
		$this->session->set_userdata('grid',$path);
		$this->data = [];
		$view = $this->config->item('view');
		$view['target'] = $this->uri->segments[1];
		$this->session->set_userdata('activePage',$this->uri->segments[3]);
		if(isset($view['session']))
		{
			foreach($view['session'] as $k=>$v)
			{
				$this->session->set_userdata($k,$v);
			}
		}
		$this->{$view['page']}($view);
	}

	private function panel($data, $class='', $add=false)
	{
		$this->grid->sectionOpen();
		$this->grid->panelTitle($data['title']);
		$this->load->model('BaseModel');

		if(isset($data['input']))
		{
			$this->grid->addInput($data['input']['name'],$data['input']['type'],date('Y-m'),$data['input']['controller'],$data['input']['label']);
		}

		if(isset($data['dropdown']['primary']))
		{
			$this->grid->addOption('dropdown-primary'.$class,$data['dropdown']['primary']['name'], $data['dropdown']['primary']['href'], $data['dropdown']['primary']['helper']($this->BaseModel->execute($data['dropdown']['primary']['arguments'])->result(), $data['dropdown']['primary']['field'], $data['dropdown']['primary']['label']));
		}

		if(isset($data['dropdown']['secondary']))
		{
			$val = $data['dropdown']['secondary'];
			$this->grid->addOption('dropdown-secondary'.$class,$val['name'], $val['controller'], $val['label'],isset($val['multiple'])?$val['multiple']:'');
		}

		if(isset($data['dropdown']['ternary']))
		{
			$this->grid->addOption('dropdown-ternary'.$class,$val['name'], $val['controller'], $val['label'],isset($val['multiple'])?$val['multiple']:'');
		}
		
		if($add == true)
		{
			$this->grid->addButton($data['target'].'/assignment','btn-ajax-insert','Tambahkan','plus',false,true);	
		}
		
		$this->navigation($data);
		$this->grid->renderHeader();

	}
	/*
	View page with insert form
	*/
	private function viewPanelSequent($data)
	{
		$this->panel($data,'-sequent');		
	}

	/*
	View page with assign data
	*/

	private function viewPanelIndependent($data)
	{
		$this->panel($data,'',true);
	}

	private function viewPanelGrid($data)
	{
		$this->load->model('BaseModel');
		$count = $this->BaseModel->execute($data['arguments'],isset($data['param'])?$data['param']:[])->num_rows();
		
		if($count<100)
		{
			$this->panelFilter($data);
			$this->grid->setPanel(isset($data['action'])?$data['action']:[], isset($data['controller'])?$data['controller']:[], isset($data['badge']));
			$this->grid->setController(isset($data['controller'])?$data['controller']:'');
			$data['content'] = $this->BaseModel->execute($data['arguments'],isset($data['param'])?$data['param']:[])->result();
			if(isset($data['cell']) && is_array($data['cell']))
			{
				foreach($data['cell'] as $key => $val)
				{
					if(isset($val['option']))
					{
						$data['cell'][$key]['option'] = $val['option']['helper']($this->BaseModel->execute($val['option']['arguments'],[], $this->data)->result(), $val['option']['field'], $val['option']['label']);
					}
				}
			}
			$this->render($data);
			$this->grid->render();
		}	
		else
		{
			echo ('serverside paging');
		}
	}

	private	function navigation($data)
	{
		if(isset($data['action']) && is_array($data['action']))
		{
			foreach($data['action'] as $val)
			{
				if($val == 'add')
				{
					$this->grid->addButton($data['target'].'/buildForm/edit/','btn-ajax','Buat Baru','file-o',true,true);

					$this->grid->addButton($data['target'].'/remove','btn-ajax-confirm-selected','Hapus','trash',true,true);
				}

				if($val == 'import')
				{
					$this->grid->addButton($data['target'].'/buildForm/import/','btn-ajax','Import','file-o',true,true);
				}

				if($val == 'trash')
				{
					$this->grid->addButton($data['target'].'/remove','btn-ajax-confirm-selected','Hapus','trash',true,true);
				}

				if($val == 'pasteFromXLS')
				{
					$this->grid->addButton('','btn-ajax-xls','Salin dari XLS','file-excel-o',true,true);
				}
				
				if($val == 'converter')
				{
					$this->grid->addButton('','btn-ajax-convert','Konverter','calculator',true,true);
				}

				if($val == 'save')
				{
					$this->grid->addButton($data['target'].'/send','btn-ajax-submit','Simpan','floppy-o',false,true);
				}

			}
		}

		$this->grid->addFilter();
		
		$this->grid->renderPanel();
		

		if(isset($data['head']) && is_array($data['head']))
		{
			foreach($data['head'] as $key=>$row)
			{
				if(strpos($row,'|'))
				{
					$this->grid->addHeader($key,$row);	
				} 
				else
				{
					$this->grid->addHeader($row);	
				}
			}
		}
		if(isset($data['subHead']))
		{
			foreach($data['subHead'] as $key=>$row)
			{
				if(strpos($row,'|'))
				{
					$this->grid->addHeader($key,$row);	
				} 
				else
				{
					$this->grid->addHeader($row);	
				}
			}
		}
	}

	private function panelFilter($data)
	{
		$this->grid->sectionOpen();
		$this->grid->panelTitle($data['title']);
		$this->grid->addFilter();
		$this->grid->renderPanel();
				if(isset($data['head']) && is_array($data['head']))
		{
			foreach($data['head'] as $key=>$row)
			{
				if(strpos($row,'|'))
				{
					$this->grid->addHeader($key,$row);	
				} 
				else
				{
					$this->grid->addHeader($row);	
				}
			}
		}
		if(isset($data['subHead']))
		{
			foreach($data['subHead'] as $key=>$row)
			{
				if(strpos($row,'|'))
				{
					$this->grid->addHeader($key,$row);	
				} 
				else
				{
					$this->grid->addHeader($row);	
				}
			}
		}
	}
	/*==================== akhir  untuk menampilkan page ====================*/

	/*
	Menampilkan grade
	*/
	public function grid()
	{
		$this->load->library('grid');
		$this->load->config($this->uri->segments[1].'/'.$this->session->grid);
		$grid = $this->config->item('grid');

		if(isset($grid['session']))
		{
			$sess = explode('-',$grid['session']);
			$val = explode('_',$this->input->post($grid['session']));
			foreach($sess as $k=>$v)
			{
				$this->session->set_userdata($v,$val[$k]);
				$this->data[$v] = $val[$k];
			}
		}
		
		$this->load->model('BaseModel');

		$grid['controller'] = $this->uri->segments[1];
		if(isset($grid['dir']))
		{
			$path = DATA . $grid['dir'];
			$grid['target'] = $grid['dir'];
			$this->session->set_userdata('location',$path);	 	
			
			if(!file_exists($path))
			{
				mkdir($path, 0777);
				@fopen($path . "/index.html", "w");
			}

		}
		$grid['content'] = $this->BaseModel->execute($grid['arguments'],isset($grid['param'])?$grid['param']:[])->result();

		if(isset($grid['cell']) && is_array($grid['cell']))
		{
			foreach($grid['cell'] as $key => $val)
			{
				if(isset($val['option']))
				{
					$grid['cell'][$key]['option'] = $val['option']['helper']($this->BaseModel->execute($val['option']['arguments'],[], $this->data)->result(), $val['option']['field'], $val['option']['label']);
				}
			}
		}
		$this->viewGrid($grid);
	}

	private	function viewGrid($data=[])
	{
		$this->grid->setPanel(isset($data['action'])?$data['action']:[], isset($data['controller'])?$data['controller']:[], isset($data['badge'])?$data['badge']:[]);
		$this->grid->setController(isset($data['controller'])?$data['controller']:'');
		$this->render($data);
		$this->grid->renderBody();
	}

	private function render($data)
	{
		$filter = [];
		$remove = [];
		
		if(is_array($data['content']))
		{
			if(count($data['content'])>0)
			{
				foreach($data['content'] as $row)
				{
					if($row->id!=null)
					
					{
						$this->grid->rowOpen($row->id,'',isset($data['badge'])?$row:false,isset($row->removeable)?$row->removeable:false,isset($data['dir'])?$data['dir']:[]);
						if(isset($row->removeable) && $row->removeable)
						{
							$remove[] = $row->id;
						}
						if(isset($data['cell']) && is_array($data['cell']))
						{
							foreach($data['cell'] as $cell)
							{
								switch($cell['type'])
								{
									case 'addNum' : $this->grid->addNum($cell['field'],$row->{$cell['field']});
										break;
									case 'addText' : $this->grid->addText($cell['field'],$row->{$cell['field']},'justify');
										break;
									case 'addPassword' : $this->grid->addPassword($cell['field'],$row->{$cell['field']},'justify');
										break;
									case 'addSelect' : 
										if(isset($cell['option'][$cell['field']]))
										{
											if($cell['option'][$cell['field']]=='constant')
											{
												$option = $this->{$cell['field']};
											}
										}
										else
										{
											$option = $cell['option'];
										}
										$this->grid->addSelect($cell['field'],$option,$row->{$cell['field']});
										break;
									case 'addFile' : $this->grid->addRead(file_exists(WRITEABLE.$cell['path'].$cell['field'].'.'
										.$cell['ext'])?'Ada':'Tidak ada');
										break;
									case 'addRead' : $this->grid->addRead($row->{$cell['field']},'justify',isset($cell['cols'])?$cell['cols']:1,isset($cell['rows'])?$cell['rows']:1);
										break;
									case 'label' : $this->grid->addRead($row->{$cell['field']},isset($cell['alignment'])?$cell['alignment']:'left',1,1);
										break;
									case 'badge' : $this->grid->addBadge($row->{$cell['field']},'left',1,1);
										break;
									case 'status' : $this->grid->addStatus($row->{$cell['field']});
										break;
									case 'sprintf' : $this->grid->addRead(sprintf($cell['label'],$cell['replacement'][0],$cell['replacement'][1]),'left',1,1);
										break;
									case 'addRadio' : $this->grid->addRadio($cell['name'],$cell['value'],'',$row->{$cell['field']} !=null && $row->{$cell['field']}==$cell['value']);
										break;
									case 'addCheck' : $this->grid->addCheck($cell['field'],$row->{$cell['field']},'',$row->{$cell['field']});
										break;
								}
							}
						}
						if(isset($data['detail']))
						{
							if(is_array($data['detail']))
							{
								$detail=explode('|',$data['detail']['content']);
								if(isset($data['detail']['decrypt']))
								{
									$content = $this->encryption->decrypt($row->{$detail[0]});
								}
								else
								{
									$content = $row->{$detail[0]};
								}
								$this->grid->addDetail($detail[0],$content,isset($detail[1]),$data['controller']);
							}
							else if($data['detail'] != null)
							{
								$detail=explode('|',$data['detail']);
								$this->grid->addDetail($detail[0],$row->{$detail[0]},isset($detail[1]),$data['controller']);
							}
							else
							{
								$this->grid->addDetail('','','',$data['target']);
							}
						}
						$this->grid->rowClose();
						$filter[] = $row->id;
					}
					else
					{
						$this->grid->addMessage('Data tidak ditemukan');	
					}
					$this->setFilter($this->session->grid,$filter);
					$this->setFilter($this->session->grid.'_remove',$remove);
				}
			}
			else
			{
				$this->grid->addMessage('Data tidak ditemukan');
			}
		}
	}

	/*==================== akhir  untuk menampilkan grid ====================*/
	/*Menampilkan form*/

	function buildForm($param='edit',$id='')
	{
		$this->load->config($this->uri->segments[1].'/'.$this->session->grid);
		$this->load->library('form');
		$form = $this->config->item('form/'.$param);
		$form['target'] = $this->uri->segments[1];
		if(isset($form['session']))
		{
			$this->session->set_userdata($form['session'],$this->input->post('parameter'));
		}
		$this->load->model('BaseModel');
		if(in_array($param, ['audio']))
		{
			$this->session->set_userdata('audio',$id);
			$this->formUpload('audio');
			//$this->uploadForm($id,$param);
			//var_dump($param);
		}
		else if(in_array($param, ['content']))
		{
			echo 'a';
		}
		else
		{
			if($id!='')
			{
				$form['arguments']['id'] = $id;
				$row = $this->BaseModel->execute($form['arguments'])->row();
				if($row)
				{
					$form['id'] = isset($form['id']) ?  
						$row->{$form['id']} :
						$this->input->post('parameter');
					$form['content'] = $row;
				}
			}
			else
			{
				$form['id'] = '';
				$form['content'] = [];
			}

			foreach($form['cell'] as $key => $val)
			{
				if($val['type'] == 'select')
				{
					if($val['option'][key($val['option'])] == 'constant')
					{
						$form['cell'][$key]['option'] = $this->{key($val['option'])};
					}
					else
					{
						$this->load->model('BaseModel');
						$form['cell'][$key]['option'] = $val['option']['helper']($this->BaseModel->execute($val['option']['arguments'])->result(),$val['option']['field'],$val['option']['label']);
					}
				}			
			}
			$this->viewForm($form,$param);
		}
	}

	private	function viewForm($data=[],$target='submit')
	{
		$this->setId($data['target'].'_'.$data['table'],isset($data['id'])?$data['id']:'');
		$this->form->set_title(isset($data['title'])?$data['title']:'');
		$this->form->set_action($data['target'].'/submit/'.$target);
		foreach($data['cell'] as $cell)
		{
			$content = $data['content']->{$cell['field']} ?? '';
			switch($cell['type'])
			{
				case 'number' : 
					$this->form->add_field($cell['field'],$cell['type'],$content,'','','',$cell['label']);
					break;
				case 'date' : 
					$content = $data['content']->{$cell['field']} ?? date("Y-m-d");
					$this->form->add_field($cell['field'],$cell['type'],$content,'','','',$cell['label']);
					break;
				case 'time' : 
					$content = $data['content']->{$cell['field']} ?? date("H:i");
					$this->form->add_field($cell['field'],$cell['type'],$content,'','','',$cell['label']);
					break;
				case 'datetime' : 
					$content = isset($data['content']->{$cell['field']}) ? (date("Y-m-d",strtotime($data['content']->{$cell['field']})).'T'.date("H:i:s",strtotime($data['content']->{$cell['field']}))):(date("Y-m-d").'T'.date("H:i:s"));
					
					$this->form->add_field($cell['field'],$cell['type'].'-local',$content,'','','',$cell['label']);
					break;
				case 'datetime-local' : 
					$content = isset($data['content']->{$cell['field']}) ? (date("Y-m-d",strtotime($data['content']->{$cell['field']})).'T'.date("H:i:s",strtotime($data['content']->{$cell['field']}))):(date("Y-m-d").'T'.date("H:i:s"));
					$this->form->add_field($cell['field'],$cell['type'],$content,'','','',$cell['label']);
					break;
				case 'textarea' :
					$this->form->add_field($cell['field'],$cell['type'],'','','','',false);
					break;
				case 'select' : 
					$this->form->add_field($cell['field'],$cell['type'],$content,$cell['option'],'','',$cell['label']);
					break;
				default : 
					$this->form->add_field($cell['field'],$cell['type'],$content,'','','',$cell['label']);
					break;
			}
		}
		$this->form->render(isset($data['saveAs'])?true:false);
	}

	private function formUpload($target)
	{
		//$this->form->set_title(isset($data['title'])?$data['title']:'');
		//$this->form->set_action($data['target'].'/submit/'.$target);
		
	}

	/*==================== akhir  untuk menampilkan form ====================*/
	
	/*
	output JSON
	*/ 
	function buildJSON($id='',$id2='')
	{
		$this->load->config($this->uri->segments[1].'/'.$this->session->grid);
		$json = $this->config->item('json');
		foreach($json['parameter'] as $key=>$val)
		{
			if($val =='get')
			{
				$parameter[$key] = $id;
			}
			else
			{
				$parameter[$key] = $this->session->{$key};
			}
			$this->session->set_userdata($key,$parameter[$key]);
		}
		$this->load->model('BaseModel');
		
		if(isset($json['type']) && in_array($json['type'], ['project','performance']))
		{
			$output['instrument'] = $this->BaseModel->execute($json['instrument'])->result();
			foreach($output['instrument'] as $k => $v)
			{
				$output['instrument'][$k]->source = encrypt($json['type'].'Instruments_'.$v->id,'r');  	
			}
			$output['score'] = $this->BaseModel->execute($json['score'])->result();
			$this->session->set_userdata('assessmentType',$json['type']);
			$output['type'] = $json['type'];
			if($json['type']=='project')
			{
				$output['student_id'] = $id2;
				$output['project_id'] = $id;
				$this->session->set_userdata('student_id',$output['student_id']);
				$this->session->set_userdata('project_id',$output['project_id']);
				$output['product_id'] = encrypt('products_'.$output['project_id'].'-'.$output['student_id'],'r');
			}
			else 
			{
				$output['student_id'] = $id2;
				$output['performance_id'] = $id;
				$output['person'] = $this->BaseModel->execute($json['person'])->row();
				$this->session->set_userdata('student_id',$output['student_id']);
				$this->session->set_userdata('performance_id',$output['performance_id']);		
			}
			
		}
		else
		{
			$data = $this->BaseModel->execute($json['quiz'])->row();
			$output['user'] = $this->uri->segments[1];
			if($this->uri->segments[1]=='teacher')
			{
				$output['quiz']['close'] = 0;
				$output['quiz']['prepare'] = 0;
				$output['quiz']['remain'] = strtotime($data->finish)-strtotime($data->start);  
			}
			else
			{
				$output['quiz']['prepare'] = strtotime($data->start)-strtotime(date('Y-m-d H:i:s'));	
				$output['quiz']['close'] = strtotime($data->finish)-strtotime(date('Y-m-d H:i:s'))>0?0:1;
				$output['quiz']['remain'] = $data->remain==null ? strtotime($data->finish)-strtotime($data->start) : $data->remain;  
			}
			$output['quiz']['start'] = date('Y-m-d H:i:s',strtotime($data->start)); 
			if($output['quiz']['close']==1)
			{
				$this->session->unset_userdata('token',$data->token);
				$this->session->unset_userdata('quiz_id',$data->id);
			}
			else
			{
				$this->session->set_userdata('token',$data->token);
				$this->session->set_userdata('quiz_id',$data->id);
				$this->session->set_userdata('remain',$output['quiz']['remain']);
			}
		}
		$this->json($output);
	}

	function quiz()
	{
		if(isset($this->session->token) && $this->input->post('token')==$this->session->token)
		{
			$this->load->config($this->uri->segments[1].'/'.$this->session->grid);
			$quiz = $this->config->item('quiz');
			$this->load->model('BaseModel');
			$output['instrument'] = $this->BaseModel->execute($quiz['instrument'])->result();
			foreach($output['instrument'] as $k => $v)
			{
				$output['instrument'][$k]->file = encrypt('quizs_'.$v->id,'r');  
			}
			$output['status'] = 1;
			$this->session->set_userdata('finish',strtotime('+'.$this->session->remain.' seconds'));
			$output['finish'] = strtotime('+'.$this->session->remain.' seconds');
		}
		else
		{
			$output['status'] = 0;
		}
		$this->json($output);		
	}

	function answer()
	{
		if(isset($this->session->token))
		{
			$this->load->config($this->uri->segments[1].'/'.$this->session->grid);
			$answer = $this->config->item('answer');
			$this->load->model('BaseModel');
			foreach($answer['field'] as $key=>$val)
			{
				$v = explode('|',$val);
				$field[$key] = $v[1]=='post' ? 
					$this->input->post($v[0]) :
					$this->session->{$v[0]};
			}
			$this->BaseModel->replace($answer['table'],$field);
			$this->json($field);
		}
	}

	function setRemain()
	{
		if(isset($this->session->token))
		{
			$this->load->model('BaseModel');
			$field['test_id'] = $this->session->quiz_id;
			$field['user_id'] = $this->session->user_id;
			$field['start_test'] = date('Y-m-d H:i:s');
			$field['remain'] = $this->session->finish-strtotime(date('Y-m-d H:i:s'));
			$this->BaseModel->replace('users_tests',$field);
		}
	}

	/*==================== akhir  untuk output JSON ====================*/
	
	function assessment()
	{
		$this->load->config($this->uri->segments[1].'/'.$this->session->grid);
		$this->load->library('post');
		$send = $this->config->item('send');
		$this->load->model('BaseModel');
		foreach($send['session'] as $val)
		{
			$field[$val] =$this->session->{$val};
		}
		foreach ($send['post'] as $key => $val) 
		{
			$field[$val] = $this->input->post($val);
		}
		$field['assessor_id'] = $this->session->user_id;

		$this->BaseModel->replace($send['table'],$field);
		$this->json($this->post->success('Nilai telah tersimpan'));		
	}

	function dropdown($param)
	{
		$this->load->config($this->uri->segments[1]);
		$dropdown = $this->config->item($param);
		$this->data = [];
		if(isset($dropdown['session']))
		{
			$sess = explode('-',$dropdown['session']);
			$val = explode('_',$this->input->post($dropdown['session']));
			foreach($sess as $k=>$v)
			{
				$this->session->set_userdata($v, $val[$k]);	
				$this->data[$v] = $val[$k];
			}
		}
		if(isset($dropdown['arguments']['bind']))
		{
			foreach($dropdown['arguments']['bind'] as $k=>$v)
			{
				$dropdown['arguments']['where'][$k] = $val[$v];
			}
		}
		
		$this->load->model('BaseModel');
		$this->json(dataOption($dropdown['helper']($this->BaseModel->execute($dropdown['arguments'])->result(), $dropdown['field'],$dropdown['label'])));
	}

	/*
	execute form
	*/
	public function submit($param='edit')
	{
		$this->load->config($this->uri->segments[1].'/'.$this->session->grid);
		$this->load->config('form_validation');
		$this->load->library(['form_validation','post']);
		$data = $this->config->item('submit/'.$param);
		$data['target'] = $this->uri->segments[1];

		if($this->post->_valid_csrf_nonce() === FALSE)
		{
			$this->json($this->post->error_csrf());
		}
		else if ($this->form_validation->run($data['rules']) === false)
		{
			$this->json($this->post->error_validation());
		}
		else
		{		
			$field = [];

			foreach($data['field'] as $key => $val)
			{
				if($val=='')
				{
					$field[$key] = sanitize($this->input->post($key));
				}
				else
				{
					if(is_array($val))
					{
						$array = [];
						foreach($val as $v)
						{
							$array[$v] = sanitize($this->input->post($v));
						}
						$field[$key] = json_encode($array);
					}
					else
					{
						if(is_integer($key))
						{
							$field[$val] = sanitize($this->input->post($val));
						}
						else
						{
							if($val=='encrypt')
							{
								$field[$key] = $this->encryption->encrypt(compress(sanitize($this->input->post($key))));
							}
							else
							{
								$field[$key] = compress(sanitize($this->input->post($key)));
							}
						}
					}
				}	
			}

			if(isset($data['session']))
			{
				if(is_array($data['session']))
				{
					foreach($data['session'] as $key => $row)
					{
						if(is_integer($key))
						{
							$field[$row] = $this->session->{$row};	
						} 
						else
						{
							$field[$key] = $this->session->{$row};	
						}
					}
				}
				else
				{
					$field[$data['session']] = $this->session->{$data['session']};
				}
			}
//			$this->debug($field);

			if($param != 'import')
			{
				$file = false;
				if(isset($data['file']))
				{
					if(! is_array($data['file'])){
						$file = [];
						$file = [
							'path' => $this->session->location . '/',
							'text' => $this->input->post($data['file']),
						];
						if(!file_exists($file['path']))
						{
							mkdir($file['path'], 0777);
						}
					}
				}
				
				if($this->input->post('saveAs')==1)
				{
					$this->insert($data['table'], $field, $data['target'], $file);
				}
				else
				{
					$this->save($data['table'], $field, $data['key'], $data['target'], $file);
				}
				//$this->debug($file);
			}
			else
			{
				$output = $this->parser($field);
				$this->load->model('BaseModel');
				if($output['status'])
				{
					foreach($output['data'] as $key=>$val)
					{
						$user_id = $this->BaseModel->insert($data['table'],$val);
						$learner = [
							'student_id'=>$user_id,
							'room_id'=>$this->session->room_id,
						];
						$group = [
							'user_id' => $user_id,
							'group_id' => $data['group'],

						];
						$this->BaseModel->insert($data['relation']['learners'],$learner);
						$this->BaseModel->insert($data['relation']['users_groups'],$group);
					}
					$this->successUpdate();	
				}
			}
		}
	}

	private	function insert($table,$field,$target,$data=false)
	{
		$this->load->model('BaseModel');
		//$this->benchmark->mark('start');
		$id=$this->BaseModel->insert($table, $field);
		$this->setId($target.'_'.$table, $id);
		$this->json($this->post->success_insert($id));
		if($data && $data['text']!= null)
		{
			$this->saveFile($data['path'], $id, $daya['text']);	
		}
		//$this->benchmark->mark('finish');
		/*$log = fopen(DATA.'lesson/benchmark_mysql_file.txt', "a+");
		@fwrite($log, "elapsed time: ".$this->benchmark->elapsed_time('start', 'finish').", file size:  \n");
		fclose($log);(/)*/
	}

	private function save($table,$field,$key,$target,$data=false)
	{
		//$this->benchmark->mark('start');
		$this->load->model('BaseModel');
		if($this->getId($target.'_'.$table)=='')
		{
			$id = $this->BaseModel->insert($table,$field);
			$this->setId($target.'_'.$table,$id);
			$this->successInsert($id);
			//$this->debug($field);
		}
		else
		{
			$id= $this->getId($target.'_'.$table);
			$this->BaseModel->update($table, $field, [$key=>$id]);
			$this->successUpdate();
			//$this->debug($target.'_'.$table);
		}
		if($data && $data['text']!= null)
		{
			$this->saveFile($data['path'], $id, $data['text']);	
		}
		//$this->benchmark->mark('finish');
		/*$log = fopen($data['path'].'benchmark_mysql_file.txt', "a+");
		@fwrite($log, "elapsed time: ".$this->benchmark->elapsed_time('start', 'finish').", file size: {memory_usage} \n");
		fclose($log);*/
	}

	private function saveFile($path, $id, $text)
	{
		$file = fopen($path.$id.'.dat', "w");
		@fwrite($file, gzcompress($text));
		fclose($file);
	}

	/*================================ end execute form ==============================*/
	
	public function Send()
	{
		$this->load->library(['post']);
		$this->load->config($this->uri->segments[1].'/'.$this->session->grid);
		$data = $this->config->item('send');
		$data['target'] = $this->uri->segments[1];
		$this->load->model('BaseModel');

		$filter = $this->getFilter($this->session->grid);

		foreach($data['field'] as $key => $val)
		{
			if(is_integer($key))
			{
				$$val = $this->input->post($val);
			}
			else
			{
				$$key = $this->input->post($key);
			}
		}

		foreach(${$data['mark']} as $key=>$mark)
		{
			if(in_array($key,$filter))
			{
				foreach($data['field'] as $k=>$val)
				{
					if(is_integer($k))
					{
						if($val == 'password')
						{
							if(${$val}[$key] != '')
							{
								$field[$key][$val] = password_hash(${$val}[$key],  PASSWORD_BCRYPT, ['cost'=>12]);
							}
						}
						else
						{
							$field[$key][$val] = ${$val}[$key];
						}
					}
					else
					{
						if($k == 'password')
						{
							if(${$k}[$key] != '')
							{
								$field[$key][$k] = password_hash(${$k}[$key],  PASSWORD_BCRYPT, ['cost'=>12]);
							}
						}
						else
						{
							$field[$key][$k] = $val(${$k}[$key]);
						}
					}
				}
				
				if(is_integer($key))
				{
					$field[$key][$data['key']] = $key;
				}
				else
				{
					$keyAssoc = explode('|',$data['key']);
					$valAssoc = explode('_',$key);
					foreach($keyAssoc as $k=>$v)
					{
						$field[$key][$v] = $valAssoc[$k];
					}
				}
			}
			
			if(isset($data['session']))
			{
				foreach($data['session'] as $k=>$v)
				{
					if(is_integer($k))
					{
						$field[$key][$v] = $this->session->{$v};
					}
					else
					{
						$field[$key][$k] = $this->session->{$v};
					}
					
				}
			}
		}
		
		if(in_array('replace',$data['action']))
		{
			foreach($field as $f)
			{
				$this->BaseModel->replace($data['table'][0],$f);
			}
			if(isset($data['mutation']) && $data['mutation']==true)
			{
				$this->successMutation();
			}
			else
			{
				$this->successUpdate();
			}
			return false;
		}
		if(in_array('update',$data['action']))
		{
			$this->BaseModel->updateBatch($data['table'][0],$field);
			$this->successUpdate();
			return false;
		}
		if(in_array('delete',$data['action']))
		{
			$where = [];
			foreach($field as $k=>$v)
			{
				if($v[$data['mark']]!=0)
				{
					$where[$data['key']] = $v[$data['key']];
					if(isset($data['unset']))
					{
						unset($field[$k][$data['unset']]);
					}
					$this->BaseModel->remove($data['table'][0],$where);
				}
				else
				{
					unset($field[$k]);
				}
				
			}
		}
		if(in_array('insert',$data['action']) && count($field)>0)
		{
			if(isset($data['insert']))
			{
				foreach($field as $k => $v)
				{
					$field[$k][key($data['insert'])] = $data['insert'][key($data['insert'])]; 
				}
			}
			$this->BaseModel->insertBatch($data['table'][1],$field);
			if(isset($data['insert']))
			{
				$this->successMutation();
			}
			else
			{
				$this->successUpdate();
			}
			//$this->debug($field);
		}
		else
		{
			$this->noProcess();
		}	
	}

	public function getFile($param,$encrypt=true)
	{
		//$this->load->config($this->uri->segments[1].'/'.$this->session->grid);
		//$sendFile = $this->config->item('file');
		if($encrypt==true)
		{
			$parameter = decrypt($param);
			$dir = explode('_',$parameter->data);
			if(!file_exists(DATA . $dir[0] .'/'))
			{
				mkdir(DATA . $dir[0], 0777 );
			}
			$path = DATA .str_replace('_','/',$parameter->data).'.dat';	
		}
		else
		{
			$path = DATA .str_replace('_','/',$param).'.dat';
		}
		$data = '';
		if(file_exists($path))
		{
			$file = fopen($path, 'r');
			$content = fread($file,filesize($path));
			$data = gzuncompress($content)==false ? $content : gzuncompress($content);
			fclose($file);
		}
		if($encrypt==true){
			echo $data;	
		} 
		else
		{
			return $data;
		}
	}

	public function sendFile()
	{
		$this->load->config($this->uri->segments[1].'/'.$this->session->grid);
		$sendFile = $this->config->item('file');
		$param = decrypt($this->input->post('id'));
		$dir = explode('_',$param->data);
		if($param->mode=='rw')
		{
			$text = gzcompress(sanitize($this->input->post('text')));
			$path = DATA .str_replace('_','/',$param->data).'.dat';
			$file = fopen($path, 'w');
			fwrite($file, $text);
			fclose($file);
		}
		echo $param->mode;
	}

	public function assignment()
	{
		$this->load->library(['post']);
		$this->load->config($this->uri->segments[1].'/'.$this->session->grid);
		$data = $this->config->item('assignment');
		$data['target'] = $this->uri->segments[1];
		$field = [];
		foreach($data['field'] as $v)
		{
			$$v = $this->input->post($v);
			foreach($$v as $k=>$w)
			{
				if($w!='')
				{
					$field[$k][$v] = $w;
				}
			}
		}
		foreach($field as $k=>$v)
		{
			$field[$k][$data['session']] = $this->input->post($data['session'])??$this->session->{$data['session']};
		}
		
		if(count($field)>0)
		{
			$this->load->model('BaseModel');
			$this->BaseModel->insertBatch($data['table'],$field);
			$this->json($this->post->success_insert_batch());
		}
		else
		{
			$this->json($this->post->fail('Tidak ada data yang ditambahkan'));
		}
	}

	function debug($data=[])
	{
		$this->json($this->post->fail(json_encode($data)));
	}

	private function successUpdate()
	{
		$this->json($this->post->success_update());
	}

	private function successInsert($id)
	{
		$this->json($this->post->success_insert($id));
	}

	private function successMutation()
	{
		$this->json($this->post->success_mutation());
	}

	private function noProcess()
	{
		$this->json($this->post->fail('Tidak ada data yang diproses'));
	}

	private function setId($filter,$id='')
	{
		$this->session->set_userdata($filter,$id);
	}

	private function getId($filter)
	{
		return $this->session->userdata($filter);
	}

	private function setFilter($filter,$read)
	{
		$this->session->set_userdata($filter,$read);
	}

	private function getFilter($filter,$sign=null)
	{
		return $this->session->userdata($filter);
	}


	/*
	Remove Action
	*/

	public function remove()
	{
		$this->load->library('post');
		$this->load->config($this->uri->segments[1].'/'.$this->session->grid);
		$remove = $this->config->item('remove');
		$this->load->model('BaseModel');
		$field = [];
		$checkbox = $this->input->post('checkbox');
		$filter = $this->getFilter($this->session->grid.'_remove');
		
		foreach($checkbox as $key=>$val)
		{
			if(in_array($key,$filter))
			{
				$field[] = $key;
			}
		}
		$where = [];
		if(isset($remove['where']))
		{
			if(is_array($remove['where']))
			{
				foreach($remove['where'] as $key =>$val)
				{
					if($val == 'session')
					{
						$where[$key] = $this->session->{$key};
					}
					else
					{
						$where[$key] = $this->input->post($key);
					}
				}
			}
		}
		//$this->debug($where);
		
		if(count($field)>0)
		{
			$this->BaseModel->removeBatch($remove['table'], $field, $where, isset($remove['id'])?$remove['id']:'id');
			$this->successMutation();
		}
		else
		{
			$this->noProcess();
		}
	}

	/*============================end of Remove =================================*/


	function mutate($filter,$field=[],$type=null)
	{
		if(count($filter)>0)
		{
			if($type==null)
			{
				$this->{$this->model}->mutate($filter,$field);
			}
			else
			{
				$this->{$this->model}->$type($filter,$field);	
			}
			$this->json($this->post->success_mutation());
		}
		else
		{
			$this->json($this->post->fail());
		}
	}
/*
	function parser($post)
	{
		$data=[];
		$join=[];
		$pattern_body="/<tbody([^`]*?)\/tbody>/";
		preg_match_all($pattern_body,str_replace("'", "`",$post['content']),$body);
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
				$filter = [];
				foreach($rows[0] as $key=>$row)
				{
					$pattern_col="/<td([^`]*?)\/td>/";
					preg_match_all($pattern_col,$row,$col);
					if(count($col[0])>= $post['usernameColumn'] && count($col[0])>= $post['nameColumn'] && count($col[0])>= $post['genderColumn'] && count($col[0])>= $post['passwordColumn'] )
					{
						$username = trim(strip_tags($col[0][$post['usernameColumn']-1])," \t\n\r\0\x0B");
						if(! in_array($username,$filter))
						{
							$field['username']= $username;
							$filter[] = $username;
							$field['original_name']=trim(str_replace('&nbsp;',' ',strip_tags($col[0][$post['nameColumn']-1]))," \t\n\r\0\x0B");
							$name=explode(' ',$field['original_name']);
							$field['first_name']=$name[0];
							$field['last_name']=$name[count($name)-1];
							$gender=strtoupper(trim(str_replace('&nbsp;',' ',strip_tags($col[0][$post['genderColumn']-1]))," \t\n\r\0\x0B"));
							$field['gender']= $gender=='L'?1:($gender=='P'?2:0);
							$field['password']=password_hash(trim(str_replace('&nbsp;',' ',strip_tags($col[0][$post['passwordColumn']-1]))," \t\n\r\0\x0B"), PASSWORD_BCRYPT, ['cost'=>12]);
							$field['email'] = $username.'@example.com';
							array_push($data,$field);
						}
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
*/
	public function json($output=[])
	{
		$this->output->set_header('HTTP/1.0 200 OK')
			->set_header('HTTP/1.1 200 OK')
			->set_header('Cache-Control: no-store, no-cache, must-revalidate')
			->set_header('Cache-Control: pos t-check=0, pre-check=0')
			->set_header('Pragma: no-cache')
			->set_content_type('application/json')
			->set_output(json_encode($output));
	}

	private function pdfDecrypt($output=[])
	{
		$this->output->set_header('HTTP/1.0 200 OK')
			->set_header('HTTP/1.1 200 OK')
			->set_header('Cache-Control: no-store, no-cache, must-revalidate')
			->set_header('Cache-Control: pos t-check=0, pre-check=0')
			->set_header('Pragma: no-cache')
			->set_content_type('application/pdf')
			->set_header('Content-Disposition: inline;filename="'.$output['name'].'"')
		    ->set_output($this->encryption->decrypt(gzuncompress($output['content'])));
	}

	private function nonPDF($output=[])
	{
		$this->output->set_header('HTTP/1.0 200 OK')
			->set_header('HTTP/1.1 200 OK')
			->set_header('Cache-Control: no-store, no-cache, must-revalidate')
			->set_header('Cache-Control: pos t-check=0, pre-check=0')
			->set_header('Pragma: no-cache')
			->set_header('Content-Disposition: attachment;filename="'.$output['name'].'"')
		    ->set_output($this->encryption->decrypt(gzuncompress($output['content'])));
	}

}

class Nosession_Controller extends Base_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('users');
		$this->load->helper('email');
		$this->load->library(['form_validation','post']);
	}

	function user()
	{
		//echo $this->controller;
		$this->load->config($this->uri->segments[1].'/'.$this->uri->segments[3]);
		$data = $this->config->item('submit');
//		echo $this->uri->segments[1]. $this->uri->segments[3];
		var_dump($data);
	}
}
