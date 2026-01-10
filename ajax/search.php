<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// Remove AJAX check temporarily for testing
// if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
//     http_response_code(403);
//     echo json_encode(['error' => 'Direct access not allowed']);
//     exit();
// }

$response = ['success' => false, 'results' => []];

try {
    // Get search parameters
    $search = isset($_GET['q']) ? trim($_GET['q']) : '';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
    
    if (empty($search) || strlen($search) < 2) {
        $response['error'] = 'Please enter at least 2 characters';
        echo json_encode($response);
        exit();
    }

    // Build search query
    $sql = "SELECT id, name, price, image, description 
            FROM products 
            WHERE (name LIKE ? OR description LIKE ?) 
            ORDER BY 
                CASE 
                    WHEN name LIKE ? THEN 1
                    WHEN description LIKE ? THEN 2
                    ELSE 3
                END,
                name ASC
            LIMIT ?";
    
    $searchTerm = "%$search%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Create short description for preview
        $short_desc = strlen($row['description']) > 80 
            ? substr($row['description'], 0, 80) . '...' 
            : $row['description'];
        
        // Highlight search term in results
        $highlighted_name = preg_replace(
            "/(" . preg_quote($search, '/') . ")/i", 
            '<mark>$1</mark>', 
            $row['name']
        );
        
        $highlighted_desc = preg_replace(
            "/(" . preg_quote($search, '/') . ")/i", 
            '<mark>$1</mark>', 
            $short_desc
        );
        
        $products[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'highlighted_name' => $highlighted_name,
            'highlighted_description' => $highlighted_desc,
            'price' => number_format($row['price'], 2),
            'image' => '../assets/images/products/' . $row['image'],
            'url' => '../pages/product-details.php?id=' . $row['id']
        ];
    }
    
    $response['success'] = true;
    $response['results'] = $products;
    $response['count'] = count($products);
    
} catch (Exception $e) {
    $response['error'] = 'Search failed: ' . $e->getMessage();
}

if (isset($stmt)) {
    $stmt->close();
}
echo json_encode($response);
?>