<?php 
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: access");
    header("Access-Control-Allow-Methods: POST");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    require __DIR__ .'../classes/Database.php';
    $db_connect = new Database();
    $con = $db_connect->dbconnect();

    $data = json_decode(file_get_contents("php://input", true));
    $returnData = [];

    try{
        // $email = isset($_GET['email']) ? $_GET['?'] : die;
        // $sql = "SELECT * FROM users WHERE email = '".$email.'"';
        $sql = "SELECT * FROM users";
        $stmt = $con->query($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(count($result)){
            $response = [
                'status' => true,
                'response' => $result,
                'message' => 'success'
            ];
            http_response_code(200);
            echo json_encode($response);
        }else{
            http_response_code(400);
            $response = [
                'status' => false,
                'message' => 'error'
            ];
            echo json_encode($response);
        }

    }catch(PDOException $e){
        echo $e->getMessage();
    }
    
?>