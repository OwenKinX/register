<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: access");
    header("Access-Control-Allow-Methods: POST");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    require __DIR__ . '../classes/Database.php';
    $db_connect = new Database();
    $con = $db_connect->dbconnect();

    function msg($success, $status, $message, $extra = [])
    {
        return array_merge([
            'success' => $success,
            'status' => $status,
            'message' => $message
        ], $extra);
    }

    // DATA FORM REQUEST
    $data = json_decode(file_get_contents("php://input", true));
    $returnData = [];

    if ($_SERVER["REQUEST_METHOD"] != "POST"): 
        $returnData = msg(0, 404, 'Page Not Found!');
        elseif (!isset($data->name) || !isset($data->surname) || !isset($data->phone) || !isset($data->email) || !isset($data->password) || !isset($data->dob) || !isset($data->gender) || !isset($data->village) || !isset($data->district) || !isset($data->province) || empty(trim($data->name)) || empty(trim($data->surname)) || empty(trim($data->email)) || empty(trim($data->password))): 
            $fields = ['fields' => ['name','surname','phone','email','password','dob','gender','village','district','province']];
            $returnData = msg(0, 442, 'Please Fill in all Required Fields!', $fields);
        else:
            $name = trim($data->name);
            $surname = trim($data->surname);
            $phone = $data->phone;
            $email = trim($data->email);
            $password = trim($data->password);
            $dob = $data->dob;
            $gender = $data->gender;
            $village = $data->village;
            $district = $data->district;
            $province = $data->province;
            $image = $data->$_FILES['image'];
            
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) : 
                $returnData = msg(0, 422, 'Invalid Email Address');
            elseif (strlen($password) < 8):
                $returnData = msg(0, 422, 'Your password must be at least 8 characters long!');
            else:
                try{
                    
                    $check_email = "SELECT `email` FROM `users` WHERE `email`= :email";
                    $check_email_stmt = $con->prepare($check_email);
                    $check_email_stmt->bindValue(':email', $email, PDO::PARAM_STR);
                    $check_email_stmt->execute();

                    if ($check_email_stmt->rowCount()) :
                        $returnData = msg(0, 422, 'This E-mail already in used!');
                    else:

                        $allow = array('jpg', 'jpeg', 'png');
                        $extension = explode(".", $image['name']);
                        $fileActExt = strtolower(end($extension));
                        $fileNew = rand() . "." . $fileActExt;
                        $filePath = "upload/images/".$fileNew;

                        if (in_array($fileActExt, $allow)) {
                            if ($img['size'] > 0 && $image['error'] == 0) {
                                move_uploaded_file($image['tmp_name'], $filePath);
                            }
                        }

                        $insert_query = "INSERT INTO `users`(name,surname,phone,email,password,dob,gender,village,district,province,image) 
                        VALUES(:name,:surname,:phone,:email,:password,:dob,:gender,:village,:district,:province,:image)";

                        $sql = $con->prepare($insert_query);
                        $sql->bindValue(':name', htmlspecialchars(strip_tags($name)), PDO::PARAM_STR);
                        $sql->bindValue(':surname', htmlspecialchars(strip_tags($surname)), PDO::PARAM_STR);
                        $sql->bindValue(':phone', $phone, PDO::PARAM_INT);
                        $sql->bindValue(':email', $email, PDO::PARAM_STR);
                        $sql->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
                        $sql->bindValue(':dob', htmlspecialchars(strip_tags($dob)), PDO::PARAM_STR);
                        $sql->bindValue(':gender', $gender, PDO::PARAM_STR);
                        $sql->bindValue(':village', $village, PDO::PARAM_STR);
                        $sql->bindValue(':district', $district, PDO::PARAM_STR);
                        $sql->bindValue(':province', $province, PDO::PARAM_STR);
                        $sql->bindParam(':image', $fileNew);
                        
                        $sql->execute();
                        $returnData = msg(1, 200, 'Registration successfully');
                    endif;
                }catch(PDOException $e){
                    $returnData = msg(0, 500, $e->getMessage());
                    echo $e->getMessage();
                }
            endif;
        endif;
    echo json_encode($returnData);
?>