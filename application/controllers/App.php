<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class App extends CI_Controller {
	
	function __construct() {

	    parent::__construct();
        $this->load->helper(array('email'));
        $this->load->model('App_model');
      
        
		$h_key= getallheaders();

        if(isset($h_key['Apikey']))
		{
			$h_key['Apikey']=$h_key['Apikey'];
		}
		if(isset($h_key['apikey']))
		{
			$h_key['Apikey']=$h_key['apikey'];
		}
        if(APP_KEY !== $h_key['Apikey'])  //check header key for authorizetion 
		{
			echo json_encode(array('data'=> array('status' =>'0' ,'message'=>"Error Invalid Api Key")));
			header('HTTP/1.0 401 Unauthorized');
			die;
		}		
    }

	public function test_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		//$data = htmlspecialchars($data);
		return $data;
	}

	private function check_login() 
	{
		return true;
		exit;
		$tableName="users";	
		
        $user_id   =	$this->test_input($this->input->post('user_id'));
        $mobile_auth_token   =	$this->input->post('mobile_auth_token');
       
		if(empty($user_id )) {
			$err =array('status'=>'0','message'=>'Please enter the User Id.');
			echo json_encode($err);exit;
		}
		if(empty($mobile_auth_token )) {
			$err = array('status'=>'0','message'=>'Please enter mobile_auth_token.');
			echo json_encode($err);exit;
		}
		$where = array('user_id' => $user_id);




		// $this->Common_model->addEditRecords('users_chat',array('on_call'=>0),array('sender_id'=>$user_id));
		// $this->Common_model->addEditRecords('users_chat',array('on_call'=>0),array('receiver_id'=>$user_id));






		$resuser=$this->Common_model->getRecords('users','*',$where,'',true);
		//$this->Common_model->addEditRecords('users',array('is_verified'=>1),array('user_id'=>$user_id));
		if(empty($resuser))
    	{
    		$err = array('status'=>'4','message'=>'Oops! Logged in is not found. Please if you can try Logging again.');
			echo json_encode($err);
			exit;
    	}

    	if($resuser['is_deleted']=='1')
    	{
    		$err = array('status'=>'4','message'=>'Sorry, Logged in user is deleted. Please try Logging again.');
			echo json_encode($err);
			exit;
    	}

    	if($resuser['mobile_auth_token']!=$mobile_auth_token)
    	{
    		$err = array('status'=>'4','message'=>'You are logged in other devices that\'s you logout from here.');
			echo json_encode($err);
			exit;
    	}
		
	}

	public function login()
	{
	    $email 	     =   $this->test_input($this->input->post('email'));
		$password    =   $this->test_input($this->input->post('password'));
		$device_id   =   $this->test_input($this->input->post('device_id'));
		$device_type =   $this->test_input($this->input->post('device_type')); 
	    
       
		 if(empty($device_type))
			{
				$err =  array('status'=>'0','message'=>'Please enter device type!');
				echo json_encode($err);
				exit;
			}else if($device_type !='Android' && $device_type !='IOS' ){
			$err =  array('status' => '0', 'message' => 'Device type must be either Android or IOS');
			echo json_encode($err); exit;
		    }

			$tableName="users"; 
		 
		    if(empty($email))
			{
				$err =  array('status'=>'0','message'=>'Please enter the email.');
				echo json_encode($err);
				exit;
			}


			if(empty($password))
			{
				$err =  array('status'=>'0','message'=>'Please enter the Password.');
				echo json_encode($err);
				exit;
			}

			
		   if(empty($device_type))
			{
				$err =  array('status'=>'0','message'=>'Please enter device type!');
				echo json_encode($err);
				exit;
			}
					
		    $password=base64_encode($password);
	        $tableName="users";
			$where = array('email' => $email,'password' => $password);
			$res=$this->Common_model->getRecords($tableName,'*',$where,'',true);

		    if(!empty($res))  
		    {
		    	if($res['status']=="Inactive")
		    	{
		    		$err =  array('status'=>'0','message'=>'Your account is Inactive, Please contact us.');
					echo json_encode($err);
					exit;
		    	}


		    	$where=array('user_id'=>$res['user_id']);
		    	$date = date('Y-m-d H:i:s');
		    	$mobile_auth_token = base64_encode(rand());
	          	$update_data = array('device_id'=>$device_id,'device_type'=>$device_type,'created'=>$date,'mobile_auth_token'=>$mobile_auth_token);
				if($resdevice=$this->Common_model->addEditRecords('users',$update_data,array('user_id'=>$res['user_id']))) {

					$res=$this->Common_model->getRecords($tableName,'*',$where,'',true);
					
					$response =  array('status'=>'1','message'=>'Login Successfully','details'=>$res);		
					
				  	echo json_encode($response);
				  	exit;	
			    } else {
			    	$err =  array('status' => '0', 'message' => 'Some error occured Please try again !!');
					echo json_encode($err);
					exit;
			    }
			}else{

				$err =  array('status' => '0', 'message' => 'Incorrect email or password');
				echo json_encode($err);
			}
			
	    
	} 


    /*=============================logout===========================================*/
	public function logout()
	{
		$device_id =    $this->test_input($this->input->post('device_id'));
        $user_id   =	$this->test_input($this->input->post('user_id'));
      
		if(empty($user_id))
		{
			$err = array('data'=> array('status'=>'0','message'=>'Please enter user id '));
			echo json_encode($err);
			exit;
		}
		if(empty($device_id))
		{
			$err = array('data'=> array('status'=>'0','message'=>'Please enter device id '));
			echo json_encode($err);
			exit;
		}

    
       	$this->Common_model->addEditRecords('users',array('device_id'=>''),array('user_id'=>$user_id));

       	if($this->language=='english'){
       		$response = array('data'=> array('status'=>'4','message'=>'Logout successful.'));
		}elseif ($this->language=='arabic') {
			$response = array('data'=> array('status'=>'4','message'=>'تم الخروج بنجاح‎'  ));
		}elseif ($this->language=='french') {
			$response = array('data'=> array('status'=>'4','message'=>'déconnexion réussie'));
		} 

		  	echo json_encode($response);	
		  	exit;
	}



	/*=============================Signup===========================================*/
	public function createCustomer()
	{
	  	$username			=	$this->test_input($this->input->post('username'));
	  	$name				=	$this->test_input($this->input->post('name'));
	  	$email				=	$this->test_input($this->input->post('email'));
	  	$mobile				=	$this->test_input($this->input->post('mobile'));
	  	$country			=	$this->test_input($this->input->post('country'));
	  	$admin				=	$this->test_input($this->input->post('admin'));
 


		if(empty($username)){
			$err = array('status' => '0', 'message' => 'Please enter username.');
			echo json_encode($err); exit;

		}else{
			if(strlen($username) > 25){
				$err = array('status' => '0', 'message' => 'Username should be maximum only 25 characters.');
				echo json_encode($err); exit;
			}

			$where = array('username' => $username);
			if($this->App_model->getRecords('customer','id',$where,'',true)) {
				$err = array('status' => '0', 'message' => 'An account already exist with similar username. Please try with another.');
				echo json_encode($err); exit;
			}  
		} 


		if(empty($name)){
			$err = array('status' => '0', 'message' => 'Please enter name.');
			echo json_encode($err); exit;

		}else{

			if(strlen($name) > 50){
				$err = array('status' => '0', 'message' => 'Name should be maximum only 50 characters.');
				echo json_encode($err); exit;
			}
		} 
		
		if(empty($email)){
			$err = array('status' => '0', 'message' => 'Please enter your email.');
			echo json_encode($err); exit;
		}else{

			if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
				$err = array('status' => '0', 'message' => 'Please enter a valid email.');
				echo json_encode($err); exit;
			}

			if(strlen($email) > 100){
				$err = array('status' => '0', 'message' => 'Email should be maximum only 100 characters.');
				echo json_encode($err); exit;
			}


	        $where = array('email' => $email);
			if($this->App_model->getRecords('customer','id',$where,'',true)) {
				$err = array('status' => '0', 'message' => 'An account already exist with similar Email ID. Please try login with another.');
				echo json_encode($err); exit;
			} 
		} 
	
		if(empty($mobile)){
			$err = array('status' => '0', 'message' => 'Please enter mobile.');
			echo json_encode($err); exit;
		}else{

			$mobile = str_replace('-','',$mobile);
		   	$where = array('mobile' => $mobile);
			if($this->App_model->getRecords('customer','id',$where,'',true)) {
				$err = array('status' => '0', 'message' => 'An account already exist with similar Mobile number. Please try login with another.');
				echo json_encode($err); exit;
			}  
		} 

		if(empty($country)){
			$err = array('status' => '0', 'message' => 'Please enter country id.');
			echo json_encode($err); exit;
		}else{

			if(!$this->App_model->getRecords('country','id',array('id'=>$country),'',true)) {
				$err = array('status' => '0', 'message' => 'Please enter valid country id.');
				echo json_encode($err); exit;
			}  

		}
		if(empty($admin)){
			$err = array('status' => '0', 'message' => 'Please enter admin id.');
			echo json_encode($err); exit;
		}else{

			if(!$this->App_model->getRecords('admin','id',array('id'=>$admin),'',true)) {
				$err = array('status' => '0', 'message' => 'Please enter valid admin id.');
				echo json_encode($err); exit;
			}  

		}
 
	    $insert_data = array( 
	    	'username'=>$username,
	    	'name' => $name,
	    	'email' => $email, 
	    	'mobile' => $mobile,
	    	'country' => $country,
	    	'admin_id' => $admin, 
	    	'created'=>date('Y-m-d h:i:s'),
	    );

		
		if($this->App_model->addEditRecords('customer',$insert_data)) {
				 
			$response = array('status'=>'1','message'=>'Customer created successfully');
		    echo json_encode($response); exit;

		} else {

			$err = array('status' => '0', 'message' => 'Server not responding. Please try again !!');
			echo json_encode($err); exit;
		}
	 
	}


	public function deleteCustomer(){

		$customer_id = $this->input->post('customer_id'); 
		if(empty($customer_id)){
            $err = array('data' =>array('status' => '0', 'message' => 'Please enter customer id.'));
            echo json_encode($err); exit;
        }else{

        	if(!$this->App_model->getRecords('customer','id',array('id'=>$customer_id),'',true)) {
				$err = array('status' => '0', 'message' => 'Please enter valid customer id.');
				echo json_encode($err); exit;
			} 
        }
		$this->App_model->deleteRecords('customer',array('id'=>$customer_id));
		$response = array('status' => '0', 'message' => 'Customer deleted successfully.');		
		echo json_encode($response); exit;
	}

	
	/*=============================Signup===========================================*/
	public function updateCustomer()
	{
	  	//$username			=	$this->test_input($this->input->post('username'));
	  	$customer_id		=	$this->test_input($this->input->post('customer_id'));
	  	$name				=	$this->test_input($this->input->post('name'));
	  	$email				=	$this->test_input($this->input->post('email'));
	  	$mobile				=	$this->test_input($this->input->post('mobile'));
	  	$country			=	$this->test_input($this->input->post('country'));
	  	$admin				=	$this->test_input($this->input->post('admin'));
 

		if(empty($customer_id)){
            $err = array('data' =>array('status' => '0', 'message' => 'Please enter customer id.'));
            echo json_encode($err); exit;
        }else{

        	if(!$this->App_model->getRecords('customer','id',array('id'=>$customer_id),'',true)) {
				$err = array('status' => '0', 'message' => 'Please enter valid customer id.');
				echo json_encode($err); exit;
			} 
        }

		if(empty($name)){
			$err = array('status' => '0', 'message' => 'Please enter name.');
			echo json_encode($err); exit;

		}else{

			if(strlen($name) > 50){
				$err = array('status' => '0', 'message' => 'Name should be maximum only 50 characters.');
				echo json_encode($err); exit;
			}
		} 
		
		if(empty($email)){
			$err = array('status' => '0', 'message' => 'Please enter your email.');
			echo json_encode($err); exit;
		}else{

			if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
				$err = array('status' => '0', 'message' => 'Please enter a valid email.');
				echo json_encode($err); exit;
			}

			if(strlen($email) > 100){
				$err = array('status' => '0', 'message' => 'Email should be maximum only 100 characters.');
				echo json_encode($err); exit;
			}


	        $where = array('email' => $email,'id!='=>$customer_id);
			if($this->App_model->getRecords('customer','id',$where,'',true)) {

				$err = array('status' => '0', 'message' => 'An account already exist with similar Email ID. Please try login with another.');
				echo json_encode($err); exit;
			} 
		} 
	
		if(empty($mobile)){
			$err = array('status' => '0', 'message' => 'Please enter mobile.');
			echo json_encode($err); exit;
		}else{

			$mobile = str_replace('-','',$mobile);
		   	$where = array('mobile' => $mobile,'id!='=>$customer_id);
			if($this->App_model->getRecords('customer','id',$where,'',true)) {
				$err = array('status' => '0', 'message' => 'An account already exist with similar Mobile number. Please try login with another.');
				echo json_encode($err); exit;
			}  
		} 

		if(empty($country)){
			$err = array('status' => '0', 'message' => 'Please enter country id.');
			echo json_encode($err); exit;
		}else{

			if(!$this->App_model->getRecords('country','id',array('id'=>$country),'',true)) {
				$err = array('status' => '0', 'message' => 'Please enter valid country id.');
				echo json_encode($err); exit;
			}  

		}
		if(empty($admin)){
			$err = array('status' => '0', 'message' => 'Please enter admin id.');
			echo json_encode($err); exit;
		}else{

			if(!$this->App_model->getRecords('admin','id',array('id'=>$admin),'',true)) {
				$err = array('status' => '0', 'message' => 'Please enter valid admin id.');
				echo json_encode($err); exit;
			}  

		}
 
	    $insert_data = array( 
	    	'name' => $name,
	    	'email' => $email, 
	    	'mobile' => $mobile,
	    	'country' => $country,
	    	'admin_id' => $admin, 
	    	'updated'=>date('Y-m-d h:i:s'),
	    );

		
		if($this->App_model->addEditRecords('customer',$insert_data,array('id'=>$customer_id)))  {
				 
			$response = array('status'=>'1','message'=>'Customer details update successfully');
		    echo json_encode($response); exit;

		} else {

			$err = array('status' => '0', 'message' => 'Server not responding. Please try again !!');
			echo json_encode($err); exit;
		}
	 
	}

	public function adminList(){

			if($admin_list = $this->App_model->getRecords('admin','*','','',false)){

				$response = array('status'=>'1','message'=>'Admin List','list'=>$admin_list);
			    echo json_encode($response); exit;

			} else {

				$err = array('status' => '0', 'message' => 'Record Not Found.');
				echo json_encode($err); exit;
			}
	}

	public function countryList(){

			if($country_list = $this->App_model->getRecords('country','*','','',false)){

				$response = array('status'=>'1','message'=>'Country List','list'=>$country_list);
			    echo json_encode($response); exit;

			} else {

				$err = array('status' => '0', 'message' => 'Record Not Found.');
				echo json_encode($err); exit;
			}
	}



	public function customerList(){

		$admin_id		=	$this->test_input($this->input->post('admin_id'));

		if(empty($admin_id)){
            $err = array('data' =>array('status' => '0', 'message' => 'Please enter admin id.'));
            echo json_encode($err); exit;
        }else{

        	if(!$this->App_model->getRecords('admin','id',array('id'=>$admin_id),'',true)) {
				$err = array('status' => '0', 'message' => 'Please enter valid admin id.');
				echo json_encode($err); exit;
			} 
        }


        if($customer_list = $this->App_model->getRecords('customer','*',array('admin_id'=>$admin_id),'id DESC',false)){

			$response = array('status'=>'1','message'=>'Customer List','list'=>$customer_list);
		    echo json_encode($response); exit;

		} else {

			$err = array('status' => '0', 'message' => 'Record Not Found.');
			echo json_encode($err); exit;
		}




	}


}


 