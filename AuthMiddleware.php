<?php
    require __DIR__ . '/classes/JwtHandler.php';

    class Auth extends JwtHandler
    {
        protected $db;
        protected $headers;
        protected $token;

        public function __construct($db, $headers)
        {
            parent::__construct();
            $this->db = $db;
            $this->headers = $headers;
        }

        public function isValid()
        {

            if (array_key_exists('Authorization', $this->headers) && preg_match('/Bearer\s(\S+)/', $this->headers['Authorization'], $matches)) {

                $data = $this->jwtDecodeData($matches[1]);

                if (
                    isset($data['data']->cus_email) &&
                    $user = $this->fetchUser($data['data']->cus_email)
                ) :
                    return [
                        "success" => 1,
                        "user" => $user
                    ];
                else :
                    return [
                        "success" => 0,
                        "message" => $data['message'],
                    ];
                endif;
            } else {
                return [
                    "success" => 0,
                    "message" => "Token not found in request"
                ];
            }
        }

        protected function fetchUser($cus_email)
        {
            try {
                $fetch_user_by_email = "SELECT `email` FROM `users` WHERE `email`=:email";
                $query_stmt = $this->db->prepare($fetch_user_by_email);
                $query_stmt->bindValue(':email', $cus_email, PDO::PARAM_INT);
                $query_stmt->execute();

                if ($query_stmt->rowCount()) :
                    return $query_stmt->fetch(PDO::FETCH_ASSOC);
                else :
                    return false;
                endif;
            } catch (PDOException $e) {
                return null;
            }
        }
    }