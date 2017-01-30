<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Grid {

	private $title='Tabel Tanpa Judul';
	private $header=[];
	private $controller;
	private $panelRow=[];
	private $message;
	private $row_header=1;
	private $rows=[];
	private $detail = [];
	private $current_row='';
	private $current_col=0;
	private $cols = 10;
	private $cell=[];
	private $max=100;
	private $min=0;
	private $_panel_open='';
	private $_nav=[];
	private $_filter=[];
	private $_btn=[];
	private $_title='';
	private $_action = 0;
	private $_opt=[];
	private $style='margin-bottom:0px';
	private $badge=[];
	private $status=[];
	private $row_order=0;
	private $rowspan=1;
	private $hint='Info! Perhatikan info ini';
	private $view='';

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->lang->load('label');
		$this->CI->load->library('session');
		$this->CI->load->helper(['form','filter']);
		$this->view = '';
	}

	public function setRow($row=0)
	{
		$this->row_order=(int)$row;
	}

	public function setHint($param='hint')
	{
		$this->hint=$param;
	}

	public function setTitle()
	{
		$this->csrf=true;
	}

	public function setRowspan($param)
	{
		$this->rowspan=$param;
	}

	public function setRange($range=null)
	{
		if($range!=null)
		{
			$this->max = $range['maximum'];
			$this->min = $range['minimum'];	
		}
	}

	public function setPanel($panel=[], $target=[], $label=[])
	{
		$this->panelRow = [
			'action' => $panel,
			'target' => $target,
			'label' => $label,
		];
	}
	public function setController($controller)
	{
		$this->controller = $controller;
	}
	
	public function addHeader($title='title',$setting='0|1|1')
	{
		$width = $title=='check'?'5px':'200px';
		$config = explode('|',$setting);
		if(!isset($this->header[$config[0]]))
		{
			$this->header[$config[0]] = [];
		}
		$title=lang($title)!=''?lang($title):$title;
		array_push($this->header[$config[0]],['title'=>$title,'colspan'=>$config[1],'rowspan'=>$config[2],'width'=>$width]);
		
	}

	public function addPointer($value='',$controller='')
	{
		$class = $controller == ''?'lock':(($value == '')?'free':'fill');
		$this->rows[$this->current_row].='<td class="pointer pointer-'.$class.'" data-href="'.$controller.'">'.$value.'</td>';
	}

	public function addCell($param='&nbsp;',$align='',$cols=1,$rows=1)
	{
		$attr=$cols>1?'colspan="'.$cols.'" ':'';
		$attr.=$rows>1?'rowspan="'.$rows.'"':'';
		$class = ($param == '&nbsp;' || $param == '')?'free':'fill';
		
		$this->rows[$this->current_row].='<td class="cell cell-'.$class.'" align="'.$align.'" '.$attr.'><p class="form-static">'.str_replace("\\'","`",$param).'</p></td>';
	}

	public function addRead($param='&nbsp;',$align='',$cols=1,$rows=1)
	{
		$attr=$cols>1?'colspan="'.$cols.'" ':'';
		$attr.=$rows>1?'rowspan="'.$rows.'"':'';
		$class = ($param == '&nbsp;' || $param == '')?'free':'fill';
		$align = is_numeric($param)?'right':$align;
		
		$this->rows[$this->current_row].='<td class="cell cell-'.$class.'" align="'.$align.'" '.$attr.'>';
		$this->rows[$this->current_row].='<span class="read" style="max-height:30px;overflow-y:auto" target="'.$this->current_row.'">'.str_replace("\\'","`",$param).'</span></td>';
	}

	public function addDetail($title,$summary='',$edit=true,$grid='')
	{
		$this->detail[$this->current_row] = [$title,$summary,$edit,$grid];
	}

	public function addArticle($title='',$content='',$align='',$cols=1,$rows=1)
	{
		$attr=$cols>1?'colspan="'.$cols.'" ':'';
		$attr.=$rows>1?'rowspan="'.$rows.'"':'';
		$this->rows[$this->current_row].='<td class="cell cell-fill" align="'.$align.'" '.$attr.'><p><h4><b>'.$title.'</b></h4></p>'.$content.'</td>';

	}

	public function addText($name='',$value=0,$readonly=false)
	{
		if(!isset($this->rows[$this->current_row])) $this->rows[$this->current_row]='';
		$this->rows[$this->current_row].='<td class="cell cell-fill"><span contenteditable="true" class="form-control editable-html record record-'.$this->current_row.'" style="height:100%" name="'.$name.'['.$this->current_row.']" index="'.$this->current_row.'">'.$value.'</span></td>';
	}

	public function addPassword($name='')
	{
		if(!isset($this->rows[$this->current_row])) $this->rows[$this->current_row]='';
		$this->rows[$this->current_row].='<td class="cell cell-fill"><input type="password" class="form-control editable-html record" name="'.$name.'['.$this->current_row.']" index="'.$this->current_row.'"></td>';
	}

	public function addHidden($name='',$value=0)
	{
		if(!isset($this->rows[$this->current_row])) $this->rows[$this->current_row]='';
		$this->rows[$this->current_row].='<input type="hidden" value="'.$value.'" name="'.$name.'['.$this->current_row.']"/>';
	}

	public function addNum($name='',$value=0,$readonly=false,$step=1)
	{
		$class=preg_replace('/\[([^`]*?)\]/','', $name);
		if(!isset($this->rows[$this->current_row])) $this->rows[$this->current_row]='';
		$this->rows[$this->current_row].='<td class="cell cell-fill"><input type="number" '.($readonly?'readonly="readonly"':'').' class="col-sm-6 input-keyup form-control record record-'.$this->current_row.' '.$class.'" index="'.$this->current_row.'" style="width:100%;text-align:right" step="'.($step).'" min="'.$this->min.'" max="'.$this->max.'" value="'.($value==null?0:$value).'" name="'.$name.'['.$this->current_row.']"/></td>';
	}

	public function addRadio($name='',$value=null,$label='',$checked=false)
	{
		if(!isset($this->rows[$this->current_row])) $this->rows[$this->current_row]='';
		$this->rows[$this->current_row].='<td class="cell cell-fill" align="center"><input type="radio" '.($checked?'checked':'').' class="radio check-list record record-'.$this->current_row.' change-list" index="'.$this->current_row.'" name="'.$name.'['.$this->current_row.']" value="'.$value.'"/>'.$label.'</td>';
	}

	public function addCheck($name='',$value=0,$label='')
	{
		if(!isset($this->rows[$this->current_row])) $this->rows[$this->current_row]='';
		$this->rows[$this->current_row].='<td class="cell cell-fill" align="center"><input type="checkbox" '.($value>0?'checked':'').' class="checkbox check-list record record-'.$this->current_row.' change-list" index="'.$this->current_row.'" name="'.$name.'['.$this->current_row.']" value="'.$value.'"/>'.$label.'</td>';
	}

	public function addSelect($name='',$option=[],$value='',$except='')
	{
		if($except!='') unset($option[$except]);
		if(!isset($this->rows[$this->current_row])) $this->rows[$this->current_row]='';
		$this->rows[$this->current_row].='<td class="cell cell-fill">'.form_dropdown($name.'['.$this->current_row.']',$option,$value,['class'=>'form-control change-list record record-'.$this->current_row.'" style="100%','index'=>$this->current_row]).'</td>';
	}

	public function addCheckbox($name='checkbox',$value=0,$caption='',$checked=false)
	{
		if(!isset($this->rows[$this->current_row])) $this->rows[$this->current_row]='';
		$this->rows[$this->current_row].='<td class="cell cell-fill" align="center" width="auto"><p style="margin:10px 0 0 0"><input type="checkbox" '.($checked?'checked':'').' index="'.$this->current_row.'" class="radio check-list" name="'.$name.'['.$this->current_row.']" value="'.$value.'"/>'.$caption.'</p></td>';
	}

	public function addBtn($target='',$caption='Unggah')
	{
		if(!isset($this->rows[$this->current_row])) $this->rows[$this->current_row]='';
		$this->rows[$this->current_row].='<td class="cell cell-fill" align="center"><a href="#" data-href="'.$target.'" data-toggle="modal" data-target="#myModal" class="btn btn-load btn-default btn-ajax">'.$caption.'</a></td>';
	}	

	public function addBadge($value)
	{
		if(!isset($this->rows[$this->current_row])) $this->rows[$this->current_row]='';
		$count=explode('|',$value);
		if($count[0]==0){
			$label='danger';
			$caption=lang('null');
		}
		else if($count[0]*1<$count[1]*1)
		{
			$label='warning';
			$caption=lang('uncomplete');
		}
		else
		{
			$label='success';
			$caption=lang('complete');
		}
		$this->rows[$this->current_row].='<td align="center" class="cell cell-fill"><span>'.$caption.' <span class="badge">'.$value.'</span></span></td>';
	}	

	public function addStatus($value)
	{
		if(!isset($this->rows[$this->current_row])) $this->rows[$this->current_row]='';
		$val=explode('|',$value);
		$caption = isset($val[1]) ? ($val[1]==1?'success':'info') : 'info'; 
		$this->rows[$this->current_row].='<td align="center" class="cell cell-fill"><span class="badge badge-'.$caption.'">'.$val[0].'</span></td>';
	}	

	public function addImage($src='')
	{
		if(!isset($this->rows[$this->current_row])) $this->rows[$this->current_row]='';
		if($src!='')
		{
			$this->rows[$this->current_row].='<td><img src="'.$src.'?'.md5(date('Y-m-d H:i:s')).'" alt="image" style="width:60px;"/></td>';
		}
		else
		{
			$this->rows[$this->current_row].='<td>&nbsp</td>';	
		}
	}	

	public function addAudio($src='',$type='ogg')
	{
		if(!isset($this->rows[$this->current_row])) $this->rows[$this->current_row]='';
		if($src!='')
		{
			$this->rows[$this->current_row].='<td width="10px"><object controls autoplay=""><embed src="data:audio/mpeg;base64,'.$src.'"></embed></object></td>';
		}
		else
		{
			$this->rows[$this->current_row].='<td>&nbsp</td>';	
		}
	}	

	public function addTest($href='',$class='btn-ajax-test',$icon='Test')
	{
		$this->rows[$this->current_row].='<td class="cell cell-fill" width="10px" align="center"><a href="#" data-href="'.$href.'" class="btn btn-default '.$class.'" style="padding:2px 6px 2px 6px;"><i class="fa fa-'.$icon.'"></i></a></td>';
	}	

	public function addPrint($target='',$caption='Cetak')
	{
		$curent=count($this->_btn);
		$this->_btn[$curent]='<td>';
        $this->_btn[$curent].='<a href="#" data-href="'.$target.'"  disabled="disabled" class="btn btn-default btn-ajax-print" style="padding:6px 6px 6px 6px">';
        $this->_btn[$curent].=$caption.'&nbsp;<i class="fa fa-print"></i>&nbsp;</a>';
		$this->_btn[$curent].='</td>';
	}	

	public function addValidate($target='',$caption='Validasi')
	{
		$curent=count($this->_btn);
		$this->_btn[$curent]='<li style="padding-left:0px;padding-right:3px;" >';
        $this->_btn[$curent].='<a href="#" data-href="'.$target.'"  disabled="disabled" class="btn btn-default btn-ajax-validate" style="padding:6px 6px 6px 6px">';
        $this->_btn[$curent].=$caption.'&nbsp;<i class="fa fa-check"></i>&nbsp;</a>';
		$this->_btn[$curent].='</li>';
	}	

	public function addPasteFromXLS()
	{
		$curent=count($this->_btn);
		$this->_btn[$curent]='<li style="padding-left:0px;padding-right:3px;" >';
        $this->_btn[$curent].='<a href="#" class="btn btn-default btn-ajax-pasteFromXls" style="padding:6px 6px 6px 6px">';
        $this->_btn[$curent].='Paste From XLS&nbsp;<i class="fa fa-file-excel-o"></i>&nbsp;</a>';
		$this->_btn[$curent].='</li>';
	}	

	public function addIcon($target='',$icon='edit',$class='btn-ajax',$toggle=true,$title='')
	{
		$data_toggle=$toggle?'data-toggle="modal" data-target="#myModal"':'';
		if(!isset($this->rows[$this->current_row])) $this->rows[$this->current_row]='';
		if(strtolower($target)=='blank')
		{
			$this->rows[$this->current_row].='';
		}
		$this->rows[$this->current_row].='<a href="#" title="'.($title==''?$icon:$title).'" data-href="'.$target.'" '.$data_toggle.' class="btn btn-default '.$class.'" style="padding:2px 6px 2px 6px;"><i class="fa fa-'.$icon.'"></i></a>';
	}	

	public function addLink($target='',$caption='Upload',$toggle=true)
	{
		if(file_exists($target))
		{
			$data_toggle=$toggle?'data-toggle="modal" data-target="#myModal"':'';
			$this->rows[$this->current_row].='<td width="10px" class="cell cell-fill" align="center"><a href="'.$target.'" '.$data_toggle.' class="btn btn-default ">'.$caption.'</a></td>';
		}
		else
		{
			$this->rows[$this->current_row].='<td> - </td>';
		}
	}	

	private function open()
	{
		$this->current_row=0;
		return '<form id="mainform"><table id="keywords" class="table table-striped table-bordered" style="margin-bottom:60px">';
	}

	private function close()
	{
		$this->current_row=0;
		$this->row_order=0;
		$this->header=[];
		return '</table></form>';
	}

	function rowOpen($id='',$offset=0,$detil=false,$removeable=false,$object='')
	{
		$this->current_row=$id;
		$this->row_order+=1;
		$this->rows[$this->current_row]='<tr rowId="'.$id.'"><td class="cell cell-fill" align="right" rowspan="'.($detil?2:1).'" style="vertical-align:middle"><span class="read">'.($this->row_order+$offset).'</span></td>';
		if($detil)
		{

			$this->rows[$this->current_row] .= '<td colspan="'.($this->cols-1).'"  style="padding:0 10px 0 10px;display:'.(count($this->panelRow['target'])==0?'none':'').'">';
			$toggle = 'data-toggle="modal" data-target="#myModal"';

			foreach($this->panelRow['action'] as $key=>$val)
			{
				if(is_numeric($key))
				{
					switch($val)
					{
						case 'select' : $this->rows[$this->current_row] .= '<span><input type="checkbox" class="check-list record record-'.$this->current_row.' change-list" '.($removeable?'':'disabled').' index="'.$this->current_row.'" name="checkbox['.$this->current_row.']" ></span>';
							break;
						default : $this->rows[$this->current_row] .= '<a href="#" title="'.$val.'" data-href="'.$this->panelRow['target'].'/buildForm/'.$val.'/'.$this->current_row.'" '.$toggle.' class="btn btn-default btn-ajax pull-left '.'" style="padding:2px 6px 2px 6px;margin:0 6px 0 6px"><i class="fa fa-'.lang($val.'_icon').'"></i></a>'; break;
					}
				}				
				else
				{
					switch ($key) {
						case 'read': $this->rows[$this->current_row] .= '<a href="#" title="'.$key.'" data-href="'.$this->panelRow['target'].'/getFile/'.encrypt($detil->{$val},'r').'" '.$toggle.' class="btn btn-default btn-ajax-read pull-left '.'" style="padding:2px 6px 2px 6px;margin:0 6px 0 6px"><i class="fa fa-'.lang($key.'_icon').'"></i></a>';
							break;
						case 'assessingProduct' : $this->rows[$this->current_row] .= (isset($detil->ongoing) && $detil->ongoing==1)?'<a href="#" title="'.$val.'" data-href="'.$this->panelRow['target'].'/buildJSON/'.$detil->{$val}.'" '.$toggle.' class="btn btn-default btn-ajax-assessment pull-left '.'" style="padding:2px 6px 2px 6px;margin:0 6px 0 6px"><span class="badge">'.($detil->assessment??'-').'</span> <i class="fa fa-'.lang($key.'_icon').'"></i></a>':'<a href="#" disabled="disabled" class="btn btn-default"><span class="badge">'.($detil->assessment??'-').'</span> <i class="fa fa-'.lang($key.'_icon').'"></i></a> '; 
							break;
						case 'assessingPerformance' : $this->rows[$this->current_row] .= (isset($detil->ongoing) && $detil->ongoing==1)?'<a href="#" title="'.$val.'" data-href="'.$this->panelRow['target'].'/buildJSON/'.$detil->{$val}.'" '.$toggle.' class="btn btn-default btn-ajax-assessment pull-left '.'" style="padding:2px 6px 2px 6px;margin:0 6px 0 6px"><span class="badge">'.($detil->assessment??'-').'</span> <i class="fa fa-'.lang($key.'_icon').'"></i></a>':'<a href="#" disabled="disabled" class="btn btn-default"><span class="badge">'.($detil->assessment??'-').'</span> <i class="fa fa-'.lang($key.'_icon').'"></i></a> ';
							break; 
						case 'edit' : $this->rows[$this->current_row] .= '<a href="#" title="'.$val.'" data-href="'.$this->panelRow['target'].'/getFile/'.encrypt($detil->{$val},'rw').'" id="'.$detil->{$val}.'" '.$toggle.' class="btn btn-default btn-ajax-text pull-left '.'" style="padding:2px 6px 2px 6px;margin:0 6px 0 6px"><i class="fa fa-edit"></i></a>'; break; 
						case 'answerQuiz' : $this->rows[$this->current_row] .= (isset($detil->answer) && $detil->ongoing==1)?'<a href="#" title="'.$val.'" data-href="'.$this->panelRow['target'].'/buildJSON/'.$detil->{$val}.'" '.$toggle.' class="btn btn-default btn-ajax-quiz pull-left '.'" style="padding:2px 6px 2px 6px;margin:0 6px 0 6px"><span class="badge">'.($detil->answer??'-').'</span> <i class="fa fa-'.lang($key.'_icon').'"></i></a>':'<a href="#" disabled="disabled" class="btn btn-default"><span class="badge">'.($detil->answer??'-').'</span> <i class="fa fa-'.lang($key.'_icon').'"></i></a> '; break;						
					}
				}
			}	
		}
		if($detil)
		{
			$this->rows[$this->current_row] .= isset($detil->status)?'<span class="pull-right">Status: <span class="badge badge-info">'.$detil->status.'</span></span>':'';
			$this->rows[$this->current_row] .= '</td></tr><tr rowId="'.$this->current_row.'">';
		}
	}

	function addMessage($message='Data tidak ditemukan')
	{
		$this->rows[0] = '<tr><td colspan="'.$this->cols.'">'.$message.'</td></tr>';
	}

	function rowClose($val=0)
	{
		$this->rows[$this->current_row].='<input type="hidden" class="sign-hidden" index="'.$this->current_row.'" id="sign_'.$this->current_row.'" value="'.$val.'" name="change['.$this->current_row.']"/>';
		$this->rows[$this->current_row].='</tr>';
		if(isset($this->detail[$this->current_row]))
		{
			$this->rows[$this->current_row].='<tr rowId="'.$this->current_row.'" >';
			$this->rows[$this->current_row].='<td colspan="'.$this->cols.'"><details class="spoiler" target="'.$this->detail[$this->current_row][3].'_'.$this->current_row.'"><summary class="summary"></summary><div contentEditable="false" index="'.$this->current_row.'" class="editor" id="'.$this->detail[$this->current_row][3].'_'.$this->current_row.'">'.compress($this->detail[$this->current_row][1],false).'</div></td>';
			$this->rows[$this->current_row].='</tr>';
		}
	}

	function render()
	{
		echo $this->DOM();
	}

	function DOM($style='')
	{
		$output=$this->open();
		$output.='<thead>';
		$output.='<tr><th class="header" width="10px" rowspan="'.count($this->header).'">#</th>';
		if($this->_action>0)
		{
			$output.='<th class="header" width="10px" style="align:center" colspan="'.($this->_action).' rowspan="'.count($this->header).'" >';
			$output.='<i class="fa fa-list"></i></th>';
		}

		foreach($this->header as $key=>$row)
		{
			$div  = count($row)>1?count($row)-1:1;
			$w=100/($div)*0.75;
			foreach($row as $col)
			{
				$output.='<th  class="header" style="max-width:"'.$w.'%" colspan="'.$col['colspan'].'" rowspan="'.$col['rowspan'].'" ><span>'.$col['title'].'</span></th>';
			}
			if($key==0){
				$output.='</tr>';
			}
		}
		$output.='</tr></thead>';
		$this->cols = $this->_action+count($this->header)+1;
		$output.='<tbody id="content">';
		foreach($this->rows as $row)
		{
			$output.=$row;
		}
		$output.='</tbody>';
		$output.=$this->close();
		return $output;
	}

	function renderHeader()
	{
		$output=$this->open('');
		if(count($this->header) > 0)
		{
			$output.='<thead>';
			$output.='<tr><th class="header" width="10px" rowspan="'.count($this->header).'">#</th>';
		
			foreach($this->header as $key=>$row)
			{
				if($key*1>0){
					$output.='<tr>';
				}
				foreach($row as $col)
				{
					$col['width'] = (in_array(strtolower($col['title']), ['status','y','t'])) ? '10px': $col['width'];
					$output.='<th class="header" width="'.$col['width'].'" colspan="'.$col['colspan'].'" class="header" rowspan="'.$col['rowspan'].'"><span>'.$col['title'].'</span></th>';
				}
				if($key==0){
					$output.='</tr>';
				}
			}
			$this->cols = count($this->header)+1;
			$output.='</tr></thead>';
			$output.='<tbody id="content">';
			$output.='</tbody>';
		}
		$output.=$this->close();
		echo $output;
	}

	function renderBody()
	{
		foreach($this->rows as $row)
		{
			echo $row;
		}
	}

	function renderRow()
	{
		
		foreach($this->rows as $row)
		{
			echo $row;
		}
	}

	function sectionOpen()
	{
		$data='<section class="content" style="padding:10px 0 0 0">';
        $data.='<div class="box" id="section" style="margin-bottom:0">';
        echo $data;
	}

	private function sectionClose()
	{
		echo '</form></div></section>';
	}

	private function panelHeadOpen()
	{
		return '<div class="box box-header" id="combo"><h4><i class="fa fa-lightbulb-o hint-btn pull-right" data-hint="'.$this->hint.'"></i></h4>';
 	}

	private function panelBodyOpen()
	{
		return '<div class="box box-body" id="table">';
 	}

	private function panelHeadClose()
	{
		return '</div>';
  	}

	function panelBodyClose()
	{
		echo '</div>';
 	}

	function panelTitle($title='Tanpa Judul')
	{
		$this->_title ='<div class="box-title">';
        $this->_title .='<label><h3 style="margin-top:0">'.$title.'</h3></label>';
 		$this->_title .='</div>'; 
	}

	private function panelMenuOpen($align='left')
	{
		$data='<div class="navbar-custom-menu" style="padding-right:10px">';
        $data.='<ul class="nav navbar-nav pull-'.$align.'" style="margin-left:15px">';
        return $data;
	}

	function addNav($target='',$class='btn-ajax',$title='title',$icon='edit',$toggle=true)
	{
		$curent=count($this->_nav);
		$data_toggle=$toggle?'data-toggle="modal" data-target="#myModal"':'';
		$this->_nav[$curent]='<li style="padding-left:0px;padding-right:3px;">';
        $this->_nav[$curent].='<a href="#" data-href="'.$target.'" '.$data_toggle.'  class="btn btn-load btn-default '.$class.'" style="padding:6px 6px 6px 6px">';
        $this->_nav[$curent].=$title.'&nbsp;<i class="fa fa-'.$icon.'"></i>&nbsp;</a>';
		$this->_nav[$curent].='</li>';
	}

	function addButton($target='',$class='btn-ajax',$title='title',$icon='edit',$toggle=true,$disable=false)
	{
		$curent=count($this->_btn);
		$dis=$disable?'disabled="disabled"':'';
		$data_toggle=($toggle==true and $disable==false)?'data-toggle="modal" data-target="#myModal"':'';
		$this->_btn[$curent]='<li style="padding-left:0px;padding-right:3px;" >';
        $this->_btn[$curent].='<a href="#" title="'.$title.'" style="width="50px" height="50px" data-href="'.$target.'" '.$data_toggle.' '.$dis. 'modal="'.$toggle.'" class="btn btn-info btn-sequent '.$class.'">';
        $this->_btn[$curent].='<i class="fa fa-'.$icon.' medium btn-icon"></i></a>';
		$this->_btn[$curent].='</li>';
	}

	function addOption($type='p',$name='',$target='',$option=array(),$multi='')
	{
		$curent=count($this->_opt);
		$extra='data-href="'.$target.'" '.$multi.' target="#content" id="dropdown_'.$curent.'" class="dropdown form-control '.$type.' col-md-5"';
		$this->_opt[$curent]='<li>';
		$this->_opt[$curent].=form_dropdown($name,$option,'',$extra);
        $this->_opt[$curent].='</li>';
	}

	function addInput($name='',$type='',$value, $target='',$label)
	{
		$curent=count($this->_opt);
		$extra='data-href="'.$target.'" target="#content" class="input-triger form-control '.$type.' col-md-5"';
		$this->_opt[$curent]='<li><label>'.$label.': </label>';
		$this->_opt[$curent].='<input type="'.$type.'" name="'.$name.'" value="'.$value.'" '.$extra.'/>';
        $this->_opt[$curent].='</li>';
	}

	function addSearch($keyword='',$target='')
	{
		$curent=count($this->_nav);
		$this->_nav[$curent]='<li>';
        $this->_nav[$curent].='<input list="keyword" value="'.$keyword.'" data-target="'.$target.'" class="autosearch form-control" data-href="'.$target.'" placeholder="search">';
 		$this->_nav[$curent].='<datalist id="keyword"><option class="list"></option>';
        $this->_nav[$curent].='</datalist>';
        $this->_nav[$curent].='</li>';             
	}

	function addFilter()
	{
		$curent=count($this->_nav);
		$this->_nav[$curent]='<li>';
        $this->_nav[$curent].='<input type="text" id="filter" class="filter form-control" placeholder="search">';
 		$this->_nav[$curent].='</li>';              
	}

	private function panelMenuClose()
	{
		return '</ul></div>';
	}

	function renderPanel()
	{
		$output=$this->panelHeadOpen();
		$output.=$this->_title;
		$this->addButton('','btn-print','Print','print',false,false);
		if(count($this->_nav)>0)
		{
			$output.=$this->panelMenuOpen('left');
			foreach($this->_nav as $row)
			{
				$output.=$row;	
			}
			$output.=$this->panelMenuClose();
		}
		if(count($this->_btn)>0)
		{
			$output.=$this->panelMenuOpen('right');
			foreach($this->_btn as $row)
			{
				$output.=$row;	
			}
			$output .=$this->panelMenuClose();
		}
		if(count($this->_opt)>0)
		{
			$output.=$this->panelMenuOpen('left');
			foreach($this->_opt as $row)
			{
				$output.=$row;
			}
			$output.=$this->panelMenuClose();
		}
		$output.=$this->panelHeadClose();
		$output.=$this->panelBodyOpen();
		echo $output;
	}
}