<?php
class readinessassment extends CI_Controller
{
	
	function __construct() {
		
		parent::__construct();		//ini_set('error_reporting', E_ALL);		//ini_set('display_errors', 'On');  //On or Off
		
		$this->load->library('form_validation');
	} 

	public  function add_readassement()
	
	{

		$this->load->library('form_validation');
		$this->form_validation->set_rules('first_name', 'First Name', 'required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'required');
		$this->form_validation->set_rules('email', 'email','required|valid_email|callback_checkifemailexist');
		$this->form_validation->set_rules('phone_no', 'Phone No', 'required');
		$this->form_validation->set_rules('address1', 'Address', 'required');
		$this->form_validation->set_rules('city', 'City', 'required');
		$this->form_validation->set_rules('state', 'State', 'required');
		$this->form_validation->set_rules('country', 'Country', 'required');

		$this->form_validation->set_error_delimiters('<div style="color: red;">', '</div>');

		if ($this->form_validation->run() == FALSE){ 
			if($this->input->post()){
				// echo "<pre>";
				// print_r($this->input->post());
				// die;
			}
			$data['content'] = 'readinessassment_insert';
			$this->load->view('layout/layoutassesment',$data);

		}else{
			

			$config['mailtype'] = 'html';
			$this->load->library('email',$config);
			$this->load->library('table');
			$this->email->from('webmaster@gpiwin.com', 'GPI');
			$verify_reg_code=uniqid();
			$first_name=$this->input->post('first_name');
			$last_name=$this->input->post('last_name');
			$zip_code=$this->input->post('zip_code');
			$organization=$this->input->post('organization');
			$email = $this->input->post('email');
			$city = ucwords($this->input->post('city'));
			$state = ucwords($this->input->post('state'));
			$country = $this->input->post('country');
			$totalscore=$this->input->post('postscore'); 
			$totalscore = sprintf("%02d", $totalscore);

			$password=mt_rand(1000, 9999);

			$getlevel=$this->gpi_model->getscore("readiness_score",$totalscore);
				//foreach($getlevel as $getlevel)
				//{
			// echo "<pre>";
			// print_r($getlevel);
			// die;
			$level_id= $getlevel->level_id;
				//}

				// $level_id=$level_id;

			$getlevelname=$this->gpi_model->getrecordbyidrow("levels",'level_id',$level_id);
			$level=$getlevelname->level_name;			
			$mem_id = $this->common_model->select_single_field('mem_id','tbl_membership',array('mem_default'=>1));
			//echo $this->db->last_query();
			$data=array(
				'first_name'=>$first_name,
				'last_name'=>$last_name,
				'email'=>$email,
				'phone_no'=>$this->input->post('phone_no'),
				'address1'=>$this->input->post('address1'),
				'zip_code'=>$zip_code,
				'city'=>$city,								
				'mem_id'=>$mem_id,
				'state'=>$state,
				'country'=>$country,
				'organization'=>$organization,
				'password'=>$password,
				'score'=>$totalscore,
				'level_id'=>$level_id,
				'verify_reg_code'=>$verify_reg_code,
				'flag'=>1,
				);

			$this->gpi_model->insert($data,'users');

     			//$this->email->to($this->input->post('email')); 
			$this->email->to($this->input->post('email')); 

			$qa="";
			$i=0;

			$answer=$this->input->post('answer_id');
			$question=$this->input->post('question');
			$start = '
			<html>
			<head>
				<title>GPI</title>
			</head>
			<body>
				<p align="center"><h1><b>Self Assessment Readiness Check</b></h1></p><br/>
				<table><h1><b>Score:'.$totalscore.'</b></h1><br/>
					<h1><b>Level:'.$level.'</b></h1></table><br/>
					<table>
						<tr>
							<td><b>First Name : </b></td>
							<td>'.$first_name.'</td>
						</tr>
						<tr>
							<td><b>Last Name : </b></td>
							<td>'.$last_name.'</td>
						</tr>
						<tr>
							<td><b>Email : </b></td>
							<td>'.$email.'</td>
						</tr>
						<tr>
							<td><b>Password : </b></td>
							<td>'.$password.'</td>
						</tr>
						<tr>
							<td><b>City : </b></td>
							<td>'.$city.'</td>
						</tr>
						<tr>
							<td><b>State : </b></td>
							<td>'.province_list($country,$state,true).'</td>
						</tr>
						<tr>
							<td><b>Country : </b></td>
							<td>'.country_list($country).'</td>
						</tr>
						<tr>
							<td><b>Zip Code : </b></td>
							<td>'.$zip_code.'</td>
						</tr>
						<tr>
							<td><b>Organization : </b></td>
							<td>'.$organization.'</td>
						</tr><br />';

						$userid = $this->db->insert_id();							
						$mid="";
						$question_no=1;
						foreach($answer as $answer)
						{

							$question_name=$this->gpi_model->get_questions("questions",$question[$i]);
							$qa.= $question_name->questions_name.":".$answer."\n";
					//$qa.= $question_name->questions_name."\n";
					//$qa.= $answer."\n";


						//	$mid.='<b>Que#'.$question_name->questions_id.':'.$question_name->questions_name.'</b><br />
							$mid.='<b>Que#'.$question_no.':'.$question_name->questions_name.'</b><br />
							Ans)'.$answer.'<br/><br/>';
							$question_no++;



$data2=array(
	'user_id'=>$userid,
	'questions' =>$question_name->questions_name,
	'answers' =>$answer,

	);
$this->gpi_model->insert($data2,'user_answer');
$i++;  


}
				 //print_r($data2);

				// exit;

$last='</table><br /><br />'.$mid.'</body></html>';
$message=$start.$last;
// echo "<pre>";
// print_r($message);
// exit;  
/* set CC to Admins */
				$where = array('id'=>1); // Event 1 = Readiness Assessment Submitted 
				$email_template = $this->gpi_model->get_by_where('email_template',$where);
				
				
				$content = ''; 
				if($email_template){
					$recipients = $email_template->admin_recipients !='' ? unserialize($email_template->admin_recipients) : array();
					if($recipients){
						//$admin_recipients = $this->gpi_model->get_by_whereIn('users',array('user_id'=>$recipients));
						//$cc = array();
						//foreach ($admin_recipients as $key => $u) {
						//	$cc[] = $u->email;
							// echo "<pre>";
							// print_r($cc);
							// die;
						//}
						//if($cc)
							//$this->email->cc('paulakwatts.pw@gmail.com');
							$this->email->cc('aliakbar1to5@gmail.com,ceo.appexos@gmail.com,paulakwatts.pw@gmail.com');
					}
					/* Replacement Pattern */
					$make_patterns = array(
						'name' => $first_name.' '.$last_name ,
						'level' => $level,
						'summary' => $message,
						);
					
					$content = $email_template->email_content;
					foreach ($make_patterns as $key => $value) {
						$content = str_replace('{{'.$key.'}}', $value, $content);
					}

					$this->email->subject($email_template->email_subject);
					

					/* Replacement Pattern */
				}else{
					$this->email->subject('Score');
					$this->email->cc('aliakbar1to5@gmail.com,ceo.appexos@gmail.com,paulakwatts.pw@gmail.com');
					//$this->email->cc("paulakwatts.pw@gmail.com,ceo.appexos@gmail.com"); 
				}
				/* set CC to Admins */
				if($content == ''){
					$content = $message;	
				}
				$this->email->message($content);
				
			
				if($_SERVER['HTTP_HOST'] == 'localhost'){
					echo $content;
					exit;
				}else
				//print_r($content);
				//exit;
				
				//$this->email->message($msgHtml);
				
				$this->email->send();
				//echo $this->email->print_debugger();
				$this->session->set_flashdata('msg', "Email Successfully Sent. Your Current Level is : ".$level);
				$this->session->set_flashdata('success', "Your Assessment is Successfully Submited.");
				redirect(base_url()."readinessassment/add_readassement/readiness-assessment.html");
				
			} 
		}

		function checkifemailexist($str) {

			$checkemailexist=$this->gpi_model->emailexist("users", "email", $str);
			if($checkemailexist) {
				$this->form_validation->set_message('checkifemailexist', 'Email already exists Please try another one.');
				return false;
			} else {
				return true;
			}
		}



	}


	




