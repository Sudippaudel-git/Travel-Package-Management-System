<?php
include '../includes/db.php'; 


function indicator($element, $set) {
    return in_array($element, $set) ? 1 : 0;
}


function jaccardSimilarity($set1, $set2) {
   
    $intersection = 0;
    foreach ($set1 as $element1) {
        foreach ($set2 as $element2) {
            $intersection += indicator($element1, $set2) * indicator($element2, $set1);
        }
    }

  
    $union = count($set1) + count($set2) - $intersection;

  
    return $union == 0 ? 0 : $intersection / $union;
}


$sql = "SELECT package_id, category_id FROM Packages WHERE status = 'active'";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);


$tourist_id = $_SESSION['tourist_id'];


$sql = "
    SELECT DISTINCT category_id 
    FROM Packages 
    INNER JOIN Bookings ON Packages.package_id = Bookings.package_id 
    WHERE Bookings.tourist_id = :tourist_id AND Bookings.status = 'Confirmed'";
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':tourist_id', $tourist_id, PDO::PARAM_INT);
$stmt->execute();
$inferred_preferences = $stmt->fetchAll(PDO::FETCH_COLUMN);


if (empty($inferred_preferences)) {
   
    $sql = "SELECT package_id FROM Packages WHERE status = 'active' ORDER BY RAND() LIMIT 5";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $default_recommendations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $_SESSION['recommendations'] = $default_recommendations;
    exit();
}


$sql = "
    SELECT package_id
    FROM Bookings
    WHERE tourist_id = :tourist_id AND status IN ('Confirmed', 'Pending')";
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':tourist_id', $tourist_id, PDO::PARAM_INT);
$stmt->execute();
$booked_packages = $stmt->fetchAll(PDO::FETCH_COLUMN);


$recommendations = [];
foreach ($packages as $package) {
    
    if (!in_array($package['package_id'], $booked_packages)) {
       
        $similarity = jaccardSimilarity($inferred_preferences, [$package['category_id']]);
        if ($similarity > 0) {
            $recommendations[$package['package_id']] = $similarity;
        }
    }
}


arsort($recommendations);


$top_recommendations = array_slice($recommendations, 0, 5, true);


$_SESSION['recommendations'] = $top_recommendations;
?>
