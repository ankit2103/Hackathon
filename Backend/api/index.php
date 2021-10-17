<?php
//pipelineworks
date_default_timezone_set("Asia/Kolkata");
// header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");
// header("Access-Control-Allow-Headers: *");
//header('content-type:application/json'); 


if (isset($_GET["p"])) {
	if ($_GET["p"] == "register") {
		register();
	} elseif ($_GET["p"] == "login") {
		login();
	} elseif ($_GET["p"] == "getProfileData") {
		getProfileData();
	} elseif ($_GET["p"] == "createProfile") {
		createProfile();
	} elseif ($_GET["p"] == "createPost") {
		createPost();
	}
	elseif ($_GET["p"] == "getData") {
		getData();
	}
	elseif ($_GET["p"] == "getPDF") {
		getPDF();
	}
	else {
		$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Invaild Request');
		echo json_encode($resultData);
	}
} else {
	$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Invaild Request');
	echo json_encode($resultData);
}


function register()
{
	$header = getallheaders();
	if (isset($header['Authkey'])) {
		require('../include/config.php');
		if ($header['Authkey'] == $Authkey) {

			$input = file_get_contents("php://input");
			$event_json = json_decode($input, true);

			if (isset($event_json['email']) && isset($event_json['password']) && !empty($event_json['email']) && !empty($event_json['password'])) {
				$email = htmlspecialchars(strip_tags($event_json['email'], ENT_QUOTES));

				$password = htmlspecialchars(strip_tags($event_json['password'], ENT_QUOTES));


				$pass = hash('sha256', $password);

				require('../include/database.php');


				$check = mysqli_query($connect, "SELECT * FROM Users WHERE email = '$email' AND password = '$pass'");

				$check = mysqli_num_rows($check);

				if ($check <= 0) {
					$emailtoken = substr(md5(time()), 0, 20);
					$response = verification_mail($email, $emailtoken);
					if ($response == true) {


						$insert = mysqli_query($connect, "INSERT INTO Users(Email,Password,Email_Token) VALUES('$email','$pass','$emailtoken')");
						$resultData = array('code' => "200", 'status' => 'success', 'message' => 'Successfully Register. Please Verify Your Email First !! Check Mail Box');
						echo json_encode($resultData);
						mysqli_close($connect);
						exit();
					} else {

						$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Something Went Wrong');
						echo json_encode($resultData);
						mysqli_close($connect);
						exit();
					}
				} else {
					$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'User Already Exist');

					echo json_encode($resultData);
					mysqli_close($connect);
					exit();
				}
			} else {
				$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Invalid request1');
				echo json_encode($resultData);

				exit();
			}
		} else {
			$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Access Denied');
			echo json_encode($resultData);
			mysqli_close($connect);
			exit();
		}
	} else {
		$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Invalid Request');
		echo json_encode($resultData);
		mysqli_close($connect);
		exit();
	}
}




function login()
{

	$header = getallheaders();
	if (isset($header['Authkey'])) {
		require('../include/config.php');
		if ($header['Authkey'] == $Authkey) {
			$input = file_get_contents("php://input");
			$event_json = json_decode($input, true);

			if (isset($event_json['email']) && isset($event_json['password']) && !empty($event_json['email']) && !empty($event_json['password'])) {
				$email = htmlspecialchars(strip_tags($event_json['email'], ENT_QUOTES));

				$password = htmlspecialchars(strip_tags($event_json['password'], ENT_QUOTES));


				$pass = hash('sha256', $password);

				require('../include/database.php');


				$query = mysqli_query($connect, "SELECT * FROM Users WHERE Email = '$email'");

				$check = mysqli_num_rows($query);

				if ($check == 1) {
					$user = mysqli_fetch_array($query);

					$verifyemail = $user['VerifyEmailStatus'];

					if ($verifyemail == 1) {
						$query1 = mysqli_query($connect, "SELECT * FROM Users WHERE Email = '$email' AND Password = '$pass'");

						$check1 = mysqli_num_rows($query1);

						if ($check1 == 1) {
							$details = mysqli_fetch_array($query1);
							$user_id = $details['ID'];
							$resultData = array('code' => "200", 'status' => 'success', 'userId' => $user_id, 'message' => 'Login Successfully');
							echo json_encode($resultData);
							mysqli_close($connect);
							exit();
						} else {
							$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'invalid password');
							echo json_encode($resultData);
							mysqli_close($connect);
							exit();
						}
					} else {

						$resultData = array('code' => "203", 'status' => 'fail', 'message' => 'Email Not Verified');
						echo json_encode($resultData);
						mysqli_close($connect);
						exit();
					}
				} else {
					$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'User Does Not Exist');
					echo json_encode($resultData);
					mysqli_close($connect);
					exit();
				}
			} else {
				$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Something Went Wrong1');
				echo json_encode($resultData);
				mysqli_close($connect);
				exit();
			}
		} else {
			$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Access Denied');
			echo json_encode($resultData);
			mysqli_close($connect);
			exit();
		}
	} else {
		$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Invalid Request');
		echo json_encode($resultData);
		mysqli_close($connect);
		exit();
	}
}

function getProfileData()
{
	$header = getallheaders();
	if (isset($header['Authkey'])) {
		require('../include/config.php');
		if ($header['Authkey'] == $Authkey) {
			$input = file_get_contents("php://input");
			$event_json = json_decode($input, true);

			if (isset($event_json['userId']) && !empty($event_json['userId'])) {
				$userid = htmlspecialchars(strip_tags($event_json['userId'], ENT_QUOTES));

				require('../include/database.php');

				$query = mysqli_query($connect, "SELECT * FROM Users WHERE ID = '$userid'");

				$check = mysqli_num_rows($query);

				if ($check == 1) {
					$query1 = mysqli_query($connect, "SELECT * FROM Profile WHERE UserID = '$userid'");
					$details = mysqli_fetch_array($query1);

					$name = $details['Name'];
					$email = $details['Email'];
					$contact = $details['Contact'];
					$dob = $details['DOB'];
					$gender = $details['Gender'];
					$education = $details['Education'];
					$city = $details['City'];
					$board = $details['Board'];

					$detail = array(
						"name" => $name,
						"email" => $email,
						"contact" => $contact,
						"dob" => $dob,
						"gender" => $gender,
						"qualification" => $education,
						"board" => $board,
						"city" => $city
					);

					$resultData = array('code' => "200", 'status' => 'success', 'details' => $detail, 'message' => 'Success');
					echo json_encode($resultData);
					mysqli_close($connect);
					exit();
				} else {
					$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'User Does Not Exist');
					echo json_encode($resultData);
					mysqli_close($connect);
					exit();
				}
			} else {
				$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Something Went Wrong');
				echo json_encode($resultData);
				mysqli_close($connect);
				exit();
			}
		} else {
			$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Access Denied');
			echo json_encode($resultData);
			mysqli_close($connect);
			exit();
		}
	} else {
		$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Invalid Request');
		echo json_encode($resultData);
		mysqli_close($connect);
		exit();
	}
}



function createProfile()
{

	$header = getallheaders();
	if (isset($header['Authkey'])) {
		require('../include/config.php');
		if ($header['Authkey'] == $Authkey) {
			$input = file_get_contents("php://input");
			$event_json = json_decode($input, true);

			if (isset($event_json['userId']) && isset($event_json['name'])   && isset($event_json['contact']) && isset($event_json['dob'])  && isset($event_json['gender']) && isset($event_json['qualification'])) {
				$userid         = htmlspecialchars(strip_tags($event_json['userId'], ENT_QUOTES));
				$name           = htmlspecialchars(strip_tags($event_json['name'], ENT_QUOTES));
				$contact        = htmlspecialchars(strip_tags($event_json['contact'], ENT_QUOTES));
				$dob            = htmlspecialchars(strip_tags($event_json['dob'], ENT_QUOTES));
				$gender         = htmlspecialchars(strip_tags($event_json['gender'], ENT_QUOTES));
				$qualification  = htmlspecialchars(strip_tags($event_json['qualification'], ENT_QUOTES));
				$board          = htmlspecialchars(strip_tags($event_json['board'], ENT_QUOTES));
				$city           = htmlspecialchars(strip_tags($event_json['city'], ENT_QUOTES));

				require('../include/database.php');

				$query = mysqli_query($connect, "SELECT * FROM Users WHERE id = '$userid'");
				$check = mysqli_num_rows($query);

				if ($check == 1) {

					$details = mysqli_fetch_array($query);
					$email  = $details['Email'];
					$query1 = mysqli_query($connect, "SELECT * FROM Profile WHERE UserID = '$userid'");
					$details = mysqli_num_rows($query1);

					if ($details == 0) {
						$query1 = mysqli_query($connect, "INSERT INTO Profile(UserID,Name,Email,Contact,DOB,Gender,Education,City,Board)values('$userid','$name','$email','$contact','$dob','$gender','$qualification','$city','$board')");


						$resultData = array('code' => "200", 'status' => 'success', 'userId' => $userid, 'message' => 'Success');
						echo json_encode($resultData);
						mysqli_close($connect);
						exit();
					} elseif ($details == 1) {
						$query1 = mysqli_query($connect, "UPDATE  Profile SET Name = '$name',Email = '$email',Contact = '$contact',DOB = '$dob',Gender ='$gender',Education = '$qualification',City = '$city',Board = '$board' WHERE  UserID ='$userid'");
						$resultData = array('code' => "200", 'status' => 'success', 'userId' => $userid, 'message' => 'Success');
						echo json_encode($resultData);
						mysqli_close($connect);
						exit();
					} else {
						$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Something Went Wrong');
						echo json_encode($resultData);
						mysqli_close($connect);
						exit();
					}
				} else {
					$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'User Does Not Exist');
					echo json_encode($resultData);
					mysqli_close($connect);
					exit();
				}
			} else {
				$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Something Went Wrong');
				echo json_encode($resultData);
				mysqli_close($connect);
				exit();
			}
		} else {
			$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Access Denied');
			echo json_encode($resultData);
			mysqli_close($connect);
			exit();
		}
	} else {
		$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Invalid Request');
		echo json_encode($resultData);
		mysqli_close($connect);
		exit();
	}
}



function createPost()
{


	$header = getallheaders();
	if (isset($header['Authkey'])) {
		require('../include/config.php');
		if ($header['Authkey'] == $Authkey) {
			$input = file_get_contents("php://input");
			$event_json = json_decode($input, true);

			if (isset($event_json['userId']) && isset($event_json['category'])   && isset($event_json['title']) && isset($event_json['description'])  && isset($event_json['filename']) && isset($event_json['base64'])) {
				$userid       = htmlspecialchars(strip_tags($event_json['userId'], ENT_QUOTES));
				$category     = htmlspecialchars(strip_tags($event_json['category'], ENT_QUOTES));
				$title        = htmlspecialchars(strip_tags($event_json['title'], ENT_QUOTES));
				$description  = htmlspecialchars(strip_tags($event_json['description'], ENT_QUOTES));
				$filename     = htmlspecialchars(strip_tags($event_json['filename'], ENT_QUOTES));
				$base64       = htmlspecialchars(strip_tags($event_json['base64'], ENT_QUOTES));
				require('../include/database.php');

				$query = mysqli_query($connect, "SELECT * FROM Users WHERE ID = '$userid'");
				$check = mysqli_num_rows($query);

				if ($check == 1) {
				    
				    $response = uploadFile($userid,$filename, $base64);

						if ($response != "false" ) {
							$query1 = mysqli_query($connect, "INSERT INTO Post(UserID,Category,Title,Description,File)values('$userid','$category','$title','$description','$response')");
							$resultData = array('code' => "200", 'status' => 'success', 'message' => 'Post Successfully');
							echo json_encode($resultData);
							mysqli_close($connect);
							exit();
						} else {
							$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Something Went Wrong');
							echo json_encode($resultData);
							mysqli_close($connect);
							exit();
						}
					
				} else {
					$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'User Does Not Exist');
					echo json_encode($resultData);
					mysqli_close($connect);
					exit();
				}
			} else {
				$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Something Went Wrong');
				echo json_encode($resultData);
				mysqli_close($connect);
				exit();
			}
		} else {
			$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Access Denied');
			echo json_encode($resultData);
			mysqli_close($connect);
			exit();
		}
	} else {
		$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Invalid Request1');
		echo json_encode($resultData);
		mysqli_close($connect);
		exit();
	}
}



function getData()
{


	$header = getallheaders();
	if (isset($header['Authkey'])) {
		require('../include/config.php');
		if ($header['Authkey'] == $Authkey) {
			$input = file_get_contents("php://input");
			$event_json = json_decode($input, true);

			if (isset($event_json['userId'])) {
				$userid       = htmlspecialchars(strip_tags($event_json['userId'], ENT_QUOTES));
			
				require('../include/database.php');

				$query = mysqli_query($connect, "SELECT * FROM Users WHERE ID = '$userid'");
				$check = mysqli_num_rows($query);

				if ($check == 1) {
				    
				    $query1 = mysqli_query($connect, "SELECT * FROM Post");
				    
				    $allData = [];
				    
				    while($row =$query1->fetch_object()){
				        $title       = $row->Title;
				        $category    = $row->Category;
				        $description = $row->Description;
				        $file        = $row->File;
				        
				        $userid        = $row->UserID;
				        
				        $query2 = mysqli_query($connect, "SELECT * FROM Profile WHERE UserID = '$userid'");
				        
				        $details = mysqli_fetch_array($query2);
				        
				        $name = $details['Name'];
				         $email = $details['Email'];
				          $Contact = $details['Contact'];
				        
				        $data = array(
				            "publisherName"=>$name,
				            "email"=>$email,
				            "contact"=>$Contact,
				            "category" => $category,
				            "title" => $title,
				            "description" => $description, 
				            "link" => "https://hotelshridadaji.in/OLX/api/Post/".$file
				            );
				        array_push($allData,$data);
				        
				        
				        
				    }

							$resultData = array('code' => "200", 'status' => 'success', 'allPost' => $allData);
							echo json_encode($resultData);
							mysqli_close($connect);
							exit();
					
					
				} else {
					$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'User Does Not Exist');
					echo json_encode($resultData);
					mysqli_close($connect);
					exit();
				}
			} else {
				$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Something Went Wrong');
				echo json_encode($resultData);
				mysqli_close($connect);
				exit();
			}
		} else {
			$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Access Denied');
			echo json_encode($resultData);
			mysqli_close($connect);
			exit();
		}
	} else {
		$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Invalid Request');
		echo json_encode($resultData);
		mysqli_close($connect);
		exit();
	}
}



function getPDF()
{


	$header = getallheaders();
	if (isset($header['Authkey'])) {
		require('../include/config.php');
		if ($header['Authkey'] == $Authkey) {
			$input = file_get_contents("php://input");
			$event_json = json_decode($input, true);

			if (isset($event_json['userId'])   && isset($event_json['filename']) && isset($event_json['base64'])) {
				$userid       = htmlspecialchars(strip_tags($event_json['userId'], ENT_QUOTES));
				
				$filename     = htmlspecialchars(strip_tags($event_json['filename'], ENT_QUOTES));
				$base64       = htmlspecialchars(strip_tags($event_json['base64'], ENT_QUOTES));
				require('../include/database.php');

				$query = mysqli_query($connect, "SELECT * FROM Users WHERE ID = '$userid'");
				$check = mysqli_num_rows($query);

				if ($check == 1) {
				    
				    $response = uploadFile($userid,$filename, $base64);

						if ($response != "false" ) {
							$query1 = mysqli_query($connect, "INSERT INTO PDF(UserID,File)values('$userid','$response')");
							$resultData = array('code' => "200", 'status' => 'success', 'link' =>  "https://hotelshridadaji.in/OLX/api/Post/".$response);
							echo json_encode($resultData);
							mysqli_close($connect);
							exit();
						} else {
							$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Something Went Wrong');
							echo json_encode($resultData);
							mysqli_close($connect);
							exit();
						}
					
				} else {
					$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'User Does Not Exist');
					echo json_encode($resultData);
					mysqli_close($connect);
					exit();
				}
			} else {
				$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Something Went Wrong');
				echo json_encode($resultData);
				mysqli_close($connect);
				exit();
			}
		} else {
			$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Access Denied');
			echo json_encode($resultData);
			mysqli_close($connect);
			exit();
		}
	} else {
		$resultData = array('code' => "201", 'status' => 'fail', 'message' => 'Invalid Request1');
		echo json_encode($resultData);
		mysqli_close($connect);
		exit();
	}
}


//Internal Function
function verification_mail($email, $emailtoken)
{

	require '../class/class.phpmailer.php';

	try {
		$phpmailer = new PHPMailer(true);
		$phpmailer->isSMTP();
		$phpmailer->Host = 'smtp.gmail.com';
		$phpmailer->SMTPAuth = true;
		$phpmailer->SMTPSecure = 'ssl';
		$phpmailer->Port = 465;
		$phpmailer->Username = 'achawda866@gmail.com';
		$phpmailer->Password = 'gotfbhhpnclmudxb';
		$phpmailer->FromName = 'Enquiry Form';
		$phpmailer->AddAddress($email); //Adds a "To" address
		$phpmailer->WordWrap = 50;
		$phpmailer->IsHTML(true); //Sets message type to HTML


		$phpmailer->AddEmbeddedImage("../images/background_2.png", "bg", "background_2.png", "base64", "application/octet-stream");
		$phpmailer->AddEmbeddedImage("../images/header3.png", "header3", "header3.png", "base64", "application/octet-stream");

		$phpmailer->Subject = 'Verify Email'; ///Sets the Subject of the message
		$phpmailer->Body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml">
		<head>
		<!--[if gte mso 9]><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
		<meta content="width=device-width" name="viewport" />
		<!--[if !mso]><!-->
		<meta content="IE=edge" http-equiv="X-UA-Compatible" />
		<!--<![endif]-->
		<title></title>
		<!--[if !mso]><!-->
		<!--<![endif]-->
		<style type="text/css">
			body {
				margin: 0;
				padding: 0;
			}

			table,
			td,
			tr {
				vertical-align: top;
				border-collapse: collapse;
			}

			* {
				line-height: inherit;
			}

			a[x-apple-data-detectors=true] {
				color: inherit !important;
				text-decoration: none !important;
			}
		</style>
		<style id="media-query" type="text/css">
			@media (max-width: 620px) {

				.block-grid,
				.col {
					min-width: 320px !important;
					max-width: 100% !important;
					display: block !important;
				}

				.block-grid {
					width: 100% !important;
				}

				.col {
					width: 100% !important;
				}

				.col_cont {
					margin: 0 auto;
				}

				img.fullwidth,
				img.fullwidthOnMobile {
					width: 100% !important;
				}

				.no-stack .col {
					min-width: 0 !important;
					display: table-cell !important;
				}

				.no-stack.two-up .col {
					width: 50% !important;
				}

				.no-stack .col.num2 {
					width: 16.6% !important;
				}

				.no-stack .col.num3 {
					width: 25% !important;
				}

				.no-stack .col.num4 {
					width: 33% !important;
				}

				.no-stack .col.num5 {
					width: 41.6% !important;
				}

				.no-stack .col.num6 {
					width: 50% !important;
				}

				.no-stack .col.num7 {
					width: 58.3% !important;
				}

				.no-stack .col.num8 {
					width: 66.6% !important;
				}

				.no-stack .col.num9 {
					width: 75% !important;
				}

				.no-stack .col.num10 {
					width: 83.3% !important;
				}

				.video-block {
					max-width: none !important;
				}

				.mobile_hide {
					min-height: 0px;
					max-height: 0px;
					max-width: 0px;
					display: none;
					overflow: hidden;
					font-size: 0px;
				}

				.desktop_hide {
					display: block !important;
					max-height: none !important;
				}
			}
		</style>
		<style id="icon-media-query" type="text/css">
			@media (max-width: 620px) {
				.icons-inner {
					text-align: center;
				}

				.icons-inner td {
					margin: 0 auto;
				}
			}
		</style>
		</head>
		<body class="clean-body" style="margin: 0; padding: 0; -webkit-text-size-adjust: 100%; background-color: #091548;">
		<!--[if IE]><div class="ie-browser"><![endif]-->
		<table bgcolor="#091548" cellpadding="0" cellspacing="0" class="nl-container" role="presentation" style="table-layout: fixed; vertical-align: top; min-width: 320px; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #091548; width: 100%;" valign="top" width="100%">
			<tbody>
				<tr style="vertical-align: top;" valign="top">
					<td style="word-break: break-word; vertical-align: top;" valign="top">
						<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" style="background-color:#091548"><![endif]-->
						<div style="background-image:url(" cid:bg");background-position:center top;background-repeat:repeat;background-color:#091548;">
							<div class="block-grid" style="min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: transparent;">
								<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
									<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-image:url("cid:bg");background-position:center top;background-repeat:repeat;background-color:#091548;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:600px"><tr class="layout-full-width" style="background-color:transparent"><![endif]-->
									<!--[if (mso)|(IE)]><td align="center" width="600" style="background-color:transparent;width:600px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top:5px; padding-bottom:15px;"><![endif]-->
									<div class="col num12" style="min-width: 320px; max-width: 600px; display: table-cell; vertical-align: top; width: 600px;">
										<div class="col_cont" style="width:100% !important;">
											<!--[if (!mso)&(!IE)]><!-->
											<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:15px; padding-right: 10px; padding-left: 10px;">
												<!--<![endif]-->
												<table border="0" cellpadding="0" cellspacing="0" class="divider" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" valign="top" width="100%">
													<tbody>
														<tr style="vertical-align: top;" valign="top">
															<td class="divider_inner" style="word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;" valign="top">
																<table align="center" border="0" cellpadding="0" cellspacing="0" class="divider_content" height="8" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid transparent; height: 8px; width: 100%;" valign="top" width="100%">
																	<tbody>
																		<tr style="vertical-align: top;" valign="top">
																			<td height="8" style="word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" valign="top"><span></span></td>
																		</tr>
																	</tbody>
																</table>
															</td>
														</tr>
													</tbody>
												</table>
												<div align="center" class="img-container center fixedwidth" style="padding-right: 0px;padding-left: 0px;">
													<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr style="line-height:0px"><td style="padding-right: 0px;padding-left: 0px;" align="center"><![endif]--><img align="center" alt="Main Image" border="0" class="center fixedwidth" src="cid:header3" style="text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; width: 232px; max-width: 100%; display: block;" title="Main Image" width="232" />
													<!--[if mso]></td></tr></table><![endif]-->
												</div>
												<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top: 10px; padding-bottom: 15px; font-family: sans-serif"><![endif]-->
												<div style="color:#ffffff;font-family:Varela Round, Trebuchet MS, Helvetica, sans-serif;line-height:1.2;padding-top:10px;padding-right:0px;padding-bottom:15px;padding-left:0px;">
													<div class="txtTinyMce-wrapper" style="font-size: 14px; line-height: 1.2; color: #ffffff; font-family: Varela Round, Trebuchet MS, Helvetica, sans-serif; mso-line-height-alt: 17px;">
														<p style="margin: 0; font-size: 30px; line-height: 1.2; word-break: break-word; text-align: center; mso-line-height-alt: 36px; margin-top: 0; margin-bottom: 0;"><span style="font-size: 30px;">Verify Your Email</span></p>
													</div>
												</div>
												<!--[if mso]></td></tr></table><![endif]-->
												<div class="mobile_hide">
													<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top: 0px; padding-bottom: 0px; font-family: sans-serif"><![endif]-->
													<div style="color:#ffffff;font-family:Varela Round, Trebuchet MS, Helvetica, sans-serif;line-height:1.5;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;">
														<div class="txtTinyMce-wrapper" style="font-size: 14px; line-height: 1.5; color: #ffffff; font-family: Varela Round, Trebuchet MS, Helvetica, sans-serif; mso-line-height-alt: 21px;">
															<p style="margin: 0; line-height: 1.5; word-break: break-word; mso-line-height-alt: 21px; margin-top: 0; margin-bottom: 0;"> </p>
															<p style="margin: 0; text-align: center; line-height: 1.5; word-break: break-word; mso-line-height-alt: 21px; margin-top: 0; margin-bottom: 0;">Someone has created a ReachMe account with this email address .</p>
															<p style="margin: 0; text-align: center; line-height: 1.5; word-break: break-word; mso-line-height-alt: 21px; margin-top: 0; margin-bottom: 0;">If this was you, click the link below to verify your email address.</p>
															<p style="margin: 0; text-align: center; line-height: 1.5; word-break: break-word; mso-line-height-alt: 21px; margin-top: 0; margin-bottom: 0;"> </p>
														</div>
													</div>
													<!--[if mso]></td></tr></table><![endif]-->
												</div>
												<div align="center" class="button-container" style="padding-top:20px;padding-right:15px;padding-bottom:20px;padding-left:15px;">
													<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-spacing: 0; border-collapse: collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;"><tr><td style="padding-top: 20px; padding-right: 15px; padding-bottom: 20px; padding-left: 15px" align="center"><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://www.example.com/" style="height:31.5pt;width:119.25pt;v-text-anchor:middle;" arcsize="58%" stroke="false" fillcolor="#ffffff"><w:anchorlock/><v:textbox inset="0,0,0,0"><center style="color:#091548; font-family:sans-serif; font-size:15px"><![endif]--><a href="https://hotelshridadaji.in/OLX/verify.php?p=' . $emailtoken . '" style="-webkit-text-size-adjust: none; text-decoration: none; display: inline-block; color: #091548; background-color: #ffffff; border-radius: 24px; -webkit-border-radius: 24px; -moz-border-radius: 24px; width: auto; width: auto; border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-bottom: 1px solid #ffffff; border-left: 1px solid #ffffff; padding-top: 5px; padding-bottom: 5px; font-family: Varela Round, Trebuchet MS, Helvetica, sans-serif; text-align: center; mso-border-alt: none; word-break: keep-all;" target="_blank"><span style="padding-left:25px;padding-right:25px;font-size:15px;display:inline-block;letter-spacing:undefined;"><span style="font-size: 16px; line-height: 2; word-break: break-word; mso-line-height-alt: 32px;"><span data-mce-style="font-size: 15px; line-height: 30px;" style="font-size: 15px; line-height: 30px;"><strong>VERIFY  EMAIL<br /></strong></span></span></span></a>
													<!--[if mso]></center></v:textbox></v:roundrect></td></tr></table><![endif]-->
												</div>
												<table border="0" cellpadding="0" cellspacing="0" class="divider" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" valign="top" width="100%">
													<tbody>
														<tr style="vertical-align: top;" valign="top">
															<td class="divider_inner" style="word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 10px; padding-right: 10px; padding-bottom: 15px; padding-left: 10px;" valign="top">
																<table align="center" border="0" cellpadding="0" cellspacing="0" class="divider_content" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 1px solid #5A6BA8; width: 60%;" valign="top" width="60%">
																	<tbody>
																		<tr style="vertical-align: top;" valign="top">
																			<td style="word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" valign="top"><span></span></td>
																		</tr>
																	</tbody>
																</table>
															</td>
														</tr>
													</tbody>
												</table>
												<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 25px; padding-left: 25px; padding-top: 10px; padding-bottom: 10px; font-family: sans-serif"><![endif]-->
												<div style="color:#7f96ef;font-family:Varela Round, Trebuchet MS, Helvetica, sans-serif;line-height:1.5;padding-top:10px;padding-right:25px;padding-bottom:10px;padding-left:25px;">
													<div class="txtTinyMce-wrapper" style="font-size: 14px; line-height: 1.5; color: #7f96ef; font-family: Varela Round, Trebuchet MS, Helvetica, sans-serif; mso-line-height-alt: 21px;">
														<p style="margin: 0; font-size: 14px; line-height: 1.5; word-break: break-word; text-align: center; mso-line-height-alt: 21px; margin-top: 0; margin-bottom: 0;"><strong>Didn’t create this account?</strong></p>
														<p style="margin: 0; font-size: 14px; line-height: 1.5; word-break: break-word; text-align: center; mso-line-height-alt: 21px; margin-top: 0; margin-bottom: 0;">You can safely ignore this message.</p>
													</div>
												</div>
												<!--[if mso]></td></tr></table><![endif]-->
												<table border="0" cellpadding="0" cellspacing="0" class="divider" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" valign="top" width="100%">
													<tbody>
														<tr style="vertical-align: top;" valign="top">
															<td class="divider_inner" style="word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;" valign="top">
																<table align="center" border="0" cellpadding="0" cellspacing="0" class="divider_content" height="30" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid transparent; height: 30px; width: 60%;" valign="top" width="60%">
																	<tbody>
																		<tr style="vertical-align: top;" valign="top">
																			<td height="30" style="word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" valign="top"><span></span></td>
																		</tr>
																	</tbody>
																</table>
															</td>
														</tr>
													</tbody>
												</table>
												<!--[if (!mso)&(!IE)]><!-->
											</div>
											<!--<![endif]-->
										</div>
									</div>
									<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
									<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
								</div>
							</div>
						</div>
						<div style="background-color:transparent;">
							<div class="block-grid" style="min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: transparent;">
								<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
									<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:transparent;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:600px"><tr class="layout-full-width" style="background-color:transparent"><![endif]-->
									<!--[if (mso)|(IE)]><td align="center" width="600" style="background-color:transparent;width:600px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top:15px; padding-bottom:15px;"><![endif]-->
									<div class="col num12" style="min-width: 320px; max-width: 600px; display: table-cell; vertical-align: top; width: 600px;">
										<div class="col_cont" style="width:100% !important;">
											<!--[if (!mso)&(!IE)]><!-->
											<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:15px; padding-bottom:15px; padding-right: 10px; padding-left: 10px;">
												<!--<![endif]-->
												<div align="center" class="img-container center fixedwidth" style="padding-right: 5px;padding-left: 5px;">
													<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr style="line-height:0px"><td style="padding-right: 5px;padding-left: 5px;" align="center"><![endif]-->
													<div style="font-size:1px;line-height:5px"> </div><img align="center" alt="Your Logo" border="0" class="center fixedwidth" src="images/logo.png" style="text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; width: 145px; max-width: 100%; display: block;" title="Your Logo" width="145" />
													<div style="font-size:1px;line-height:5px"> </div>
													<!--[if mso]></td></tr></table><![endif]-->
												</div>
												<table border="0" cellpadding="0" cellspacing="0" class="divider" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" valign="top" width="100%">
													<tbody>
														<tr style="vertical-align: top;" valign="top">
															<td class="divider_inner" style="word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 15px; padding-right: 10px; padding-bottom: 15px; padding-left: 10px;" valign="top">
																<table align="center" border="0" cellpadding="0" cellspacing="0" class="divider_content" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 1px solid #5A6BA8; width: 60%;" valign="top" width="60%">
																	<tbody>
																		<tr style="vertical-align: top;" valign="top">
																			<td style="word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" valign="top"><span></span></td>
																		</tr>
																	</tbody>
																</table>
															</td>
														</tr>
													</tbody>
												</table>
												<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 15px; padding-left: 15px; padding-top: 15px; padding-bottom: 15px; font-family: sans-serif"><![endif]-->
												<div style="color:#4a60bb;font-family:Varela Round, Trebuchet MS, Helvetica, sans-serif;line-height:1.2;padding-top:15px;padding-right:15px;padding-bottom:15px;padding-left:15px;">
													<div class="txtTinyMce-wrapper" style="line-height: 1.2; font-size: 12px; font-family: Varela Round, Trebuchet MS, Helvetica, sans-serif; color: #4a60bb; mso-line-height-alt: 14px;">
														<p style="margin: 0; font-size: 12px; line-height: 1.2; text-align: center; word-break: break-word; mso-line-height-alt: 14px; margin-top: 0; margin-bottom: 0;"><span style="">Copyright © 2021 ReachMe, All rights reserved.<br /><br />Where to find us: reachme@gmail.com</span></p>
														<p style="margin: 0; font-size: 12px; line-height: 1.2; text-align: center; word-break: break-word; mso-line-height-alt: 14px; margin-top: 0; margin-bottom: 0;"><span style=""><br />Changed your mind? You can <a href="http://www.example.com" rel="noopener" style="text-decoration: underline; color: #7f96ef;" target="_blank" title="unsubscribe">unsubscribe</a> at any time.</span></p>
													</div>
												</div>
												<!--[if mso]></td></tr></table><![endif]-->
												<div style="font-size:16px;text-align:center;font-family:Varela Round, Trebuchet MS, Helvetica, sans-serif">
													<div style="height-top: 20px;"> </div>
												</div>
												<!--[if (!mso)&(!IE)]><!-->
											</div>
											<!--<![endif]-->
										</div>
									</div>
									<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
									<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
								</div>
							</div>
						</div>
						<div style="background-color:transparent;">
							<div class="block-grid" style="min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: transparent;">
								<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
									<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:transparent;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:600px"><tr class="layout-full-width" style="background-color:transparent"><![endif]-->
									<!--[if (mso)|(IE)]><td align="center" width="600" style="background-color:transparent;width:600px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;"><![endif]-->
									<div class="col num12" style="min-width: 320px; max-width: 600px; display: table-cell; vertical-align: top; width: 600px;">
										<div class="col_cont" style="width:100% !important;">
											<!--[if (!mso)&(!IE)]><!-->
											<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">
												<!--<![endif]-->
												<table cellpadding="0" cellspacing="0" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" valign="top" width="100%">
													<tr style="vertical-align: top;" valign="top">
														<td align="center" style="word-break: break-word; vertical-align: top; padding-top: 5px; padding-right: 0px; padding-bottom: 5px; padding-left: 0px; text-align: center;" valign="top">
															<!--[if vml]><table align="left" cellpadding="0" cellspacing="0" role="presentation" style="display:inline-block;padding-left:0px;padding-right:0px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"><![endif]-->
															<!--[if !vml]><!-->
															<table cellpadding="0" cellspacing="0" class="icons-inner" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; display: inline-block; margin-right: -4px; padding-left: 0px; padding-right: 0px;" valign="top">
																<!--<![endif]-->

															</table>
														</td>
													</tr>
												</table>
												<!--[if (!mso)&(!IE)]><!-->
											</div>
											<!--<![endif]-->
										</div>
									</div>
									<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
									<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
								</div>
							</div>
						</div>
						<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
					</td>
				</tr>
			</tbody>
			</table>
			<!--[if (IE)]></div><![endif]-->
			</body>
			</html>';

		if ($phpmailer->send()) {
			return true;
			exit;
		} else {
			return false;
		}
	} catch (Exception $e) {
		print_r($e);
	}
}


function uploadFile($userid,$filename, $base64)
{

	$ext = pathinfo($filename, PATHINFO_EXTENSION);

	$MainFileName = $userid . "_" . rand(10000, 99999) ."_".time(). "." . $ext;
	$data = base64_decode($base64);
	
	if (file_put_contents('Post/'.$MainFileName, $data)) {
		return  $MainFileName;
	} else {
		return false;
	}
}
