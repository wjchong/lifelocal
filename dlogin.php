<?php
	  	$mobile_number ="1159223660";
   	$password = "123";
   	$countrycode ="60";
   	$user_role = "2";   
   	  $cm =	$countrycode.''.$mobile_number;

       // print_R($_POST);
   	// die;
    	function updateStatus($session_id,$setup_session,$id,$active_login,$cash_system,$parentid){
   		setcookie("session_id", $session_id, time() + 3600 * 24 * 30 * 12 * 10,"/");
   		// Set User Cookie
   		$salt=md5(mt_rand());
   		$my_cookie_id = hash_hmac('sha512', $session_id, $salt);
   		$t_sql = "INSERT INTO pcookies SET user_id = '$id', cookie_id = '$my_cookie_id', salt = '$salt'";
   		setcookie("my_cookie_id", $my_cookie_id, time() + 3600 * 24 * 30 * 12 * 10,"/");
   		setcookie("my_token", $session_id, time() + 3600 * 24 * 30 * 12 * 10,"/");
    		$cm = $GLOBALS['cm'];
    		$password = $GLOBALS['password'];
   		$date = date('Y-m-d H:i:s');
   		$dateutc=strtotime($date);
    		$conn = $GLOBALS['conn'];
    		$token = bin2hex(openssl_random_pseudo_bytes(64));
   	    // echo $setup_session;
   		// die;
   		if($cash_system=="on")
   		{
   			// echo "SELECT id FROM cash_system WHERE is_active='y' AND id='$id'";
   			// die;
   			$pastcash = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,balance_setup FROM cash_system WHERE is_active='y' AND user_id='$parentid'"));
			// print_R($pastcash);
			// die;
   			if($pastcash)
   			{
				$balance_setup=$pastcash['balance_setup'];
				if($balance_setup=='n')
				$cash_id=$pastcash['id'];
				$_SESSION['cash_id']=$cash_id;	
   			}
   			else
   			{
   				 $cashq="INSERT INTO cash_system (`user_id`, `login_time`) VALUES ('$parentid', '$dateutc')";
   			  mysqli_query($conn,$cashq);
   			   $cash_id=mysqli_insert_id($conn);
   			   // $cash_id=$pastcash['id'];
   			 $_SESSION['cash_id']=$cash_id;	
   			}
   		}
		// print_R($_SESSION);
		// die;
   		if($setup_session=="y")
   		{
   		   if($active_login=="n")
   		   {
   				$sql = "UPDATE users SET already_login='y',shop_open='1',session = '$session_id', token = '$token',last_login='$dateutc',active_login='y' WHERE mobile_number = '$cm' AND password = '$password'";
   			 $s2="INSERT INTO user_login (`user_id`,`login_time`) VALUES ('$id', '$dateutc')";
   			 $_SESSION['last_login']=$dateutc;
   				mysqli_query($conn,$s2);
   		  }
   			else if($active_login=="y")
   			$sql = "UPDATE users SET already_login='y',shop_open='1',session = '$session_id', token = '$token' WHERE mobile_number = '$cm' AND password = '$password'";
   		}
   		else
   		{
   			if($active_login=="n")
   			{
   				$sql = "UPDATE users SET session = '$session_id', token = '$token',last_login='$dateutc',active_login='y' WHERE mobile_number = '$cm' AND password = '$password'";	
   				$s2="INSERT INTO user_login(`user_id`,`login_time`) VALUES ('$id', '$dateutc')";
   				  $cash_id=mysqli_insert_id($conn);
   				$_SESSION['cash_id']=$cash_id;
   				$_SESSION['last_login']=$dateutc;
   				mysqli_query($conn,$s2);
   			}
   			else if($active_login=="y")
   			$sql = "UPDATE users SET session = '$session_id', token = '$token' WHERE mobile_number = '$cm' AND password = '$password'";		
   		}   
   		// die;
   		if(mysqli_query($conn, $sql) && mysqli_query($conn, $t_sql)){
   			return true;
   		}else{
   			return false;
   		}
		
   	}
   	$error = "";
   	if($mobile_number == "" )
   	{
   		$error .= "Mobile Number is not Valid.<br>";
   	}
   	$query1 = mysqli_query($conn, "SELECT * FROM users WHERE mobile_number='$cm' AND user_roles = '$user_role'");
   	if($query1){
   		$user_row1 = mysqli_num_rows($query1);
   	}
   	if($user_row1 == 0)
   	{
   			$error .= "Account not found, do you want to signup?.<br>";
   	}   
   	$user_row2 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE mobile_number='$cm'AND password='$password'")); 
   	if($user_row2 == 0)
   		{
   				$error .= "You have entered wrong password, please try again.<br>";
   		}  
   	//~ if(!($user_role === $user_row2['user_roles'])){
   		//~ // echo $user_role . " <---> " . $user_row2['user_roles'];
   		//~ $error .= "Invalid type of account.";
   	//~ }
   	// if($user_row2['already_login'] == "y")
   	// {
   		// $error .= "Already Login on different browser or session, Logout From there.<br>";
   	// }
   	if($user_row2['isLocked'] == "1" && $user_row2['verification_code'] != "" )
   	{
   		$error .= "User registration pending, Please through the link sent to your mobile number?.<br>";
   	}
   	//~ if($count == 0)
   	//~ {
   		//~ $error .= "Account does not exists in our Database.<br>";
   	//~ } 
   	// if(strlen($password) >= 15 || strlen($password) <= 5)
   	// {
   		// $error .= "Password must be between 6 and 15.<br>";
   	// }
   	
   	if(empty($error))
   	{
   		$time=time();	
		// echo "SELECT parentid,user_roles,cash_system,id,isLocked,referral_id,name, mobile_number,setup_shop,active_login FROM users WHERE mobile_number='$cm' AND password='$password' AND user_roles = '$user_role'";
		// die;   
   		$user_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT parentid,user_roles,cash_system,id,isLocked,referral_id,name, mobile_number,setup_shop,active_login FROM users WHERE mobile_number='$cm' AND password='$password' AND user_roles = '$user_role'"));
   		// print_R($user_row);
   		// die;
   		 $id = $user_row['id'];
   		$parentid=$id;
   		$referral_id = $user_row['referral_id'];
   		$name = $user_row['name'];
   		$mobile_number = $user_row['mobile_number'];
   		$setup_session = $user_row['setup_shop'];
   		$active_login = $user_row['active_login'];
   		$cash_system = $user_row['cash_system'];
   		  $user_role = $user_row['user_roles'];
   		if($user_role=='5')
   		{
   			 $parentid=$user_row['parentid'];
   			$parent_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$parentid'"));
   			$cash_system=$parent_data['cash_system'];
   		}
   		// echo $cash_system;
   		// die;
   		// $_SESSION['setup_shop'] = $setup_session;
   		if(!isset($cookie_id) || !isset($session_token)){
   				$session_id =  uniqid($id . "_",true);
   				setcookie("session_id", $session_id, time() + 3600 * 24 * 30 * 12 * 10,"/");
   				$_SESSION['login']=$id;
   				$_SESSION['cash_allow']='y';
   				$_SESSION['user_id']=$id;
   			if(updateStatus($session_id,$setup_session,$id,$active_login,$cash_system,$parentid)){
   				//lucky
   				//$insert="insert into stafflogin set staff_id='$id',logintime='$time',session_id='$session_id'";
   				//mysqli_query($conn,$insert);
   				//Added by bala 02-08-2019
   				/*if($user_role == '5')
   				{
   					$login_date = date('Y-m-d');
   					$login_time = date('Y-m-d H:i:s');
   					$active = 1;
   					$sql_staff_activity_logs = "INSERT INTO staff_activity_logs 
   			    			(
   			    			user_id, 
   			    			login_date, 
   			    			login_time, 
   			    			session_id,
   			    			active
   			    			)
   							vales
   							(
   							'$id', 
   			    			'$login_date', 
   			    			'$login_time', 
   			    			'$session_id',
   			    			'$active'
   							)";
   					mysqli_query($conn,$sql_staff_activity_logs);
   				}
   				*/
				if($user_role=='2')
   		    	header("location:orderview.php");
			     else 
				header("location:dashboard.php");	 
				// header("location:dashboard.php");	 
   			}else{
   				echo "An error occuried, please, try again later.";
   			}
   		}
   		if($id)
   		{
   		    if($user_row['isLocked'] == "0")
       		{
   				$_SESSION['login'] = $id;
   				$_SESSION['user_id'] = $id;
   				$_SESSION['setup_shop'] = $setup_shop;
   				$_SESSION['referral_id'] = $referral_id;
   				$_SESSION['name'] = $name;
				$_SESSION['login_user_role'] = $user_role;
   				$_SESSION['mobile'] = $mobile_number;
       		}
       		else
       		{
       			$error .= "Sorry, the user account is blocked, please contact support.<br>";
       		}
   		}
   		else
   		{
   			$error .= "Authentication failed. You entered an incorrect username or password.<br>";
   		}
   	}

?>