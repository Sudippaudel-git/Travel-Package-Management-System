<?php
include '../includes/db.php'; // Database connection

// Function to calculate Jaccard similarity using the indicator function
function jaccardSimilarity($set1, $set2) {
    // Indicator function: returns 1 if element is in the set, 0 otherwise
    function indicator($element, $set) {
        return in_array($element, $set) ? 1 : 0;
    }

    // Calculate intersection
    $intersection = 0;
    foreach ($set1 as $element1) {
        foreach ($set2 as $element2) {
            $intersection += indicator($element1, $set2) * indicator($element2, $set1);
        }
    }

    // Calculate union
    $union = count($set1) + count($set2) - $intersection;

    // Return Jaccard similarity
    return $union == 0 ? 0 : $intersection / $union;
}

// Fetch all active packages
$sql = "SELECT package_id, category_id FROM Packages WHERE status = 'active'";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch tourist id
$tourist_id = $_SESSION['tourist_id'];

// Infer preferences based on past bookings
$sql = "
    SELECT DISTINCT category_id 
    FROM Packages 
    INNER JOIN Bookings ON Packages.package_id = Bookings.package_id 
    WHERE Bookings.tourist_id = :tourist_id AND Bookings.status = 'Confirmed'";
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':tourist_id', $tourist_id, PDO::PARAM_INT);
$stmt->execute();
$inferred_preferences = $stmt->fetchAll(PDO::FETCH_COLUMN);

// If no preferences are inferred, show default recommendations
if (empty($inferred_preferences)) {
    // Fetch default recommendations (e.g., top 5 packages by rating)
    $sql = "SELECT package_id FROM Packages WHERE status = 'active' ORDER BY rating DESC LIMIT 5";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $default_recommendations = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $_SESSION['recommendations'] = $default_recommendations;

    foreach ($default_recommendations as $package_id) {
        echo "Default Package ID: $package_id<br>";
    }
    exit();
}

// Fetch booked packages to avoid recommending already booked ones
$sql = "
    SELECT package_id
    FROM Bookings
    WHERE tourist_id = :tourist_id AND status IN ('Confirmed', 'Pending')
";
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':tourist_id', $tourist_id, PDO::PARAM_INT);
$stmt->execute();
$booked_packages = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Compute similarities and filter out booked packages
$recommendations = [];
foreach ($packages as $package) {
    // Avoid recommending booked packages
    if (!in_array($package['package_id'], $booked_packages)) {
        // Calculate Jaccard similarity between inferred preferences and the package's category
        $similarity = jaccardSimilarity($inferred_preferences, [$package['category_id']]);
        if ($similarity > 0) {
            $recommendations[$package['package_id']] = $similarity;
        }
    }
}

// Sort recommendations by similarity in descending order
arsort($recommendations);

// Get top 5 recommendations
$top_recommendations = array_slice($recommendations, 0, 5, true);

// Store recommendations in session
$_SESSION['recommendations'] = $top_recommendations;

// Display top recommendations
foreach ($top_recommendations as $package_id => $similarity) {
    echo "Recommended Package ID: $package_id, Similarity: $similarity<br>";
}
?>
