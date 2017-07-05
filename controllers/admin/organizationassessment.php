<?php
 class organizationassessment extends CI_Controller
  {
	 
    
	public  function add_organizationassessment()
	
	  {
		  
		   	$this->load->library('form_validation');
	       	$this->form_validation->set_rules('first_name', 'First Name', 'required');
		   	$this->form_validation->set_rules('last_name', 'Last Name', 'required');
		   	$this->form_validation->set_rules('email', 'email', 'required|valid_email|callback_checkifemailexist');
		    $this->form_validation->set_rules('phone_no', 'Phone No', 'required');
		   	$this->form_validation->set_rules('address1', 'Address', 'required');
		   	$this->form_validation->set_rules('city', 'City', 'required');
		   	$this->form_validation->set_rules('state', 'State', 'required');
		   	$this->form_validation->set_rules('country', 'Country', 'required');
		   	
		  	   
		   $this->form_validation->set_error_delimiters('<div style="color: red;">', '</div>');
		   
		   if ($this->form_validation->run() == FALSE)
			{
				
			  	 $data['content'] = 'organizationassessment_insert';
	   			 $this->load->view('layout/layoutassesment',$data);
		   		
			}
	 
			else{
				$verify_reg_code=uniqid();
				$config['mailtype'] = 'html';
				$this->load->library('email',$config);
				$this->load->library('table');
				
				$this->email->from('no-reply@gpiwin.com', 'GPI');
  
 		        $first_name=$this->input->post('first_name');
				$last_name=$this->input->post('last_name');
				$city = ucwords($this->input->post('city'));
				$state = ucwords($this->input->post('state'));
				$country = $this->input->post('country');
				$zip_code=$this->input->post('zip_code');
				$organization=$this->input->post('organization');
				$email=$this->input->post('email');
			 	$totalscore=$this->input->post('postscore'); 
				$totalscore = sprintf("%02d", $totalscore);
				$password=mt_rand(1000, 9999);
				
				 
		
				$getlevel=$this->gpi_model->getscore("organization_score",$totalscore);
				/*foreach($getlevel as $getlevel)
				{*/
					$level_id= $getlevel->level_id;
				//}
				//$level_id=$level_id;
				 $getlevelname=$this->gpi_model->getrecordbyidrow("levels",'level_id',$level_id);
				 $level=$getlevelname->level_name;
			/*	if($totalscore>=0 && $totalscore<=17)
					   {
						  $level_id=1;
						  $level="1-2"; 
					   }
					   else if($totalscore>=18 && $totalscore<=35)
					   {
						   $level_id=2;
						   $level="3-4"; 
					   }
					   else if($totalscore>=36 && $totalscore<=45)
					   {
						  $level_id=3;
						  $level="5-6"; 
					   }
						else
					   {
						  $level_id=4;
						  $level="7-8"; 
					   }
				*/
				$data=array(
						       'first_name'=>$first_name,
							   'last_name'=>$last_name,
		   			           'email'=>$email,
							    'phone_no'=>$this->input->post('phone_no'),
							   'address1'=>$this->input->post('address1'),
							   'city'=>$city,
					           'state'=>$state,
					           'country'=>$country,
					           'zip_code'=>$zip_code,
							   'organization'=>$organization,
							   'password'=>$password,
							   'score'=>$totalscore,
							   'level_id'=>$level_id,
							   'verify_reg_code'=>$verify_reg_code,
							   'flag'=>5,
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
								<p align="center"><h1><b>Organization Assessment Check</b></h1></p><br/>
								<table align="center"><h1><b>Score:'.$totalscore.'</b></h1><br/>
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
		  	 foreach($answer as $answer)
				{
				   
					$question_name=$this->gpi_model->get_questions("organization_questions",$question[$i]);
					$qa.= $question_name->questions_name.":".$answer."\n";
					//$qa.= $question_name->questions_name."\n";
					//$qa.= $answer."\n";
					
				
				    $mid.='<b>Que#'.$question_name->questions_id.':'.$question_name->questions_name.'</b><br />
							Ans)'.$answer.'<br/><br/>';
                    
					  
					
					$data2=array(
		   			'user_id'=>$userid,
					'questions' =>$question_name->questions_name,
					'answers' =>$answer,
				
		     		);
					 $this->gpi_model->insert($data2,'user_answer_organization');
					 $i++;  
					
		   		
				}
				 //print_r($data2);
				// exit;
				$last='</table><br /><br />'.$mid.'</body></html>';
				$message=$start.$last;
				//print_r($message);
				//exit;
				/* set CC to Admins */
				/* class recommendation */
				$if_city = $this->gpi_model->get_by_where('cities',array('name'=>$city));
				$if_where  = array(
							'city_id'=>$city,
							'state_id'=>$state,
							'country_id'=>$country,
							'level_id'=>$level_id,
							);
				$city_id = $city;
				if($if_city){
					if(is_array($if_city))
						$if_where['city_id'] = $if_city[0]->id;
					else{
						$if_where['city_id'] = $if_city->id;
					}
				}
				/* search if class recommendation */
				$if_class = $this->gpi_model->get_by_where('classes',$if_where);
				
				if($if_class){
					if(is_array($if_class))
						$recommen_class = $if_class[0]->class_name;
					else
						$recommen_class = $if_class->class_name;
				}else{
					$recommen_class = 'Please contact to administrator for class recommendation.';
				}
				/* class recommendation */
				$where = array('id'=>3); // Event 1 = Readiness Assessment Submitted 
				$email_template = $this->gpi_model->get_by_where('email_template',$where);
				$content = ''; 
				if($email_template){
					$recipients = $email_template->admin_recipients !='' ? unserialize($email_template->admin_recipients) : array();
					if($recipients){
						$admin_recipients = $this->gpi_model->get_by_whereIn('users',array('user_id'=>$recipients));
						$cc = array();
						foreach ($admin_recipients as $key => $u) {
							$cc[] = $u->email;
						}
						if($cc)
							$this->email->cc($cc);
					}
					/* Replacement Pattern */
					$make_patterns = array(
						'name' => $first_name.' '.$last_name ,
						'class' => $recommen_class,
						'level' => $level_id,
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
					$this->email->cc("paulakwatts.pw@gmail.com, ceo.appexos@gmail.com"); 
				}
				/* set CC to Admins */
			    $this->email->message($content);
				if($_SERVER['HTTP_HOST'] == 'localhost'){
					echo $content;
					exit;
				}else
					$this->email->send();

			    /*$this->email->message($message);
				$this->email->send();*/
				//echo $this->email->print_debugger();
				$this->session->set_flashdata('msg', "Email Successfully Sent. Your Current Level is : ".$level);
				$this->session->set_flashdata('success', "Your Assessment is Successfully Submited.");
				
				redirect(base_url()."organizationassessment/add_organizationassessment/organizationassessment.html");
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
	   		
	 
	
	  
  
  
  
