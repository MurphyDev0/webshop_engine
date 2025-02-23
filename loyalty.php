<?php

class LoyaltySystem {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function addPoints($user_id, $points) {
        $stmt = $this->conn->prepare("UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ?");
        $stmt->bind_param("ii", $points, $user_id);
        return $stmt->execute();
    }
    
    public function getPoints($user_id) {
        $stmt = $this->conn->prepare("SELECT loyalty_points FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            return $user['loyalty_points'];
        }
        
        return 0;
    }
    
    public function usePoints($user_id, $points) {
        if ($this->getPoints($user_id) >= $points) {
            $stmt = $this->conn->prepare("UPDATE users SET loyalty_points = loyalty_points - ? WHERE id = ?");
            $stmt->bind_param("ii", $points, $user_id);
            return $stmt->execute();
        }
        return false;
    }
}

?>