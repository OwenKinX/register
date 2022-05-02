<?php 
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: access");
    header("Access-Control-Allow-Methods: POST");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    require __DIR__. '../classes/Database.php';
    require __DIR__. '../classes/JwtHandler.php';

    $db_connect = new Database();
    $con = $db_connect->dbConnect();

    function msg($success,$status,$message,$extra = []){
        return array_merge([
            'success' => $success,
            'status' => $status,
            'message' => $message
        ],$extra);
    }

    $data = json_decode(file_get_contents("php://input", true));
    $returnData = [];

    if($_SERVER["REQUEST_METHOD"] != "POST"):
        $returnData = msg(0,404,'Page Not Found!');
    elseif(
        !isset($data->email) 
        || !isset($data->password)
        || empty(trim($data->email))
        || empty(trim($data->password))
        ):
        $fields = ['fields' => ['email','password']];
        $returnData = msg(0,442,'Please Fill in all Required Fields!',$fields);
    else:
        $email = trim($data->email);
        $password = trim($data->password);
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)):
            $returnData = msg(0,422,'Invalid Email Address!');
        elseif(strlen($password) < 8):
            $returnData = msg(0,422,'Your password must be at least 8 characters long!');
        else:
            try{
                $fetch_user_by_email = "SELECT * FROM users WHERE email =:email";
                $query_stmt = $con->prepare($fetch_user_by_email);
                $query_stmt->bindValue(':email', $email ,PDO::PARAM_STR);
                $query_stmt->execute();
    
                // IF THE USER IS FOUNDED BY EMAIL
                if($query_stmt->rowCount()):
                    $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                    $check_password = password_verify($password, $row['password']);
    
                    // VERIFYING THE PASSWORD (IS CORRECT OR NOT?)
                    // IF PASSWORD IS CORRECT THEN SEND THE LOGIN TOKEN
                    if($check_password):
    
                        $jwt = new JwtHandler();
                        $token = $jwt->jwtEncodeData(
                            'http://localhost/laogw-auth-api/',
                            array("cus_id" => $row['cus_id'])
                        );
                        
                        $returnData = [
                            'success' => 1,
                            'message' => 'You have successfully logged in.',
                            'token' => $token
                        ];
    
                    // IF INVALID PASSWORD
                    else:
                        $returnData = msg(0,400,'Invalid Password!');
                    endif;
    
                // IF THE USER IS NOT FOUNDED BY EMAIL THEN SHOW THE FOLLOWING ERROR
                else:
                    $returnData = msg(0,400,'Invalid Email Address!');
                endif;
            }
            catch(PDOException $e){
                $returnData = msg(0,500,$e->getMessage());
                echo $e->getMessage();
            }
        endif;
    endif;
    echo json_encode($returnData);
?>