<?php
/**
 * Complete Flow Analysis - Análisis completo del flujo desde búsqueda hasta analytics
 * URL: https://tracktraster.3mas1r.com/flow_analysis.php?debug_key=flow_debug_2025
 */

$debugKey = $_GET['debug_key'] ?? '';
if ($debugKey !== 'flow_debug_2025') {
    die('Access denied. Use ?debug_key=flow_debug_2025');
}

echo "<h1>🔄 Complete Flow Analysis - TrackTraster</h1>";
echo "<style>
body { font-family: Arial; margin: 20px; line-height: 1.6; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
.step { background: #e7f3ff; padding: 15px; border-left: 4px solid #007bff; margin: 10px 0; }
.success { color: #28a745; }
.error { color: #dc3545; }
.warning { color: #ffc107; }
pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";

try {
    // Load environment variables from .env
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $envVars = parse_ini_file($envFile);
        foreach ($envVars as $key => $value) {
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }
    }
    
    // Get database connection details from .env
    $dbHost = $_ENV['DB_HOSTNAME'] ?? 'localhost';
    $dbName = $_ENV['DB_DATABASE'] ?? 'tracktraster_db';
    $dbUser = $_ENV['DB_USERNAME'] ?? 'root';
    $dbPass = $_ENV['DB_PASSWORD'] ?? '';
    
    echo "<p><strong>Database Config:</strong> Host: $dbHost, Database: $dbName, User: $dbUser</p>";
    
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='step'><h2>📊 STEP 1: Current Database State</h2></div>";
    
    // Get latest tracking
    $stmt = $pdo->query("
        SELECT at.id, at.user_id, at.artist_id, a.name as artist_name, 
               at.event_name, at.created_at, at.status as tracking_status,
               a.spotify_id, a.platforms_data
        FROM artist_trackings at 
        JOIN artists a ON at.artist_id = a.id 
        WHERE at.status = 'active'
        ORDER BY at.created_at DESC LIMIT 1
    ");
    $latestTracking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$latestTracking) {
        echo "<p class='error'>❌ No active trackings found</p>";
        exit;
    }
    
    echo "<h3>Latest Tracking:</h3>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    foreach ($latestTracking as $key => $value) {
        echo "<tr><td><strong>$key</strong></td><td>" . htmlspecialchars($value) . "</td></tr>";
    }
    echo "</table>";
    
    echo "<div class='step'><h2>🔍 STEP 2: Analytics Query Test</h2></div>";
    
    $userId = $latestTracking['user_id'];
    $artistId = $latestTracking['artist_id'];
    
    // Test the exact query from Analytics controller
    echo "<h3>Testing Analytics Query (from Analytics.php line 42):</h3>";
    $analyticsQuery = "
        SELECT DISTINCT a.id, a.name, a.image_url, at.event_name, at.event_date,
                at.tracking_status, at.tracking_start_date,
                DATEDIFF(COALESCE(at.event_date, DATE_ADD(CURDATE(), INTERVAL 30 DAY)), CURDATE()) as days_to_event
         FROM artists a 
         JOIN artist_trackings at ON a.id = at.artist_id 
         WHERE at.user_id = ? AND at.status = 'active'
         ORDER BY at.event_date ASC, a.name
    ";
    
    echo "<pre>" . htmlspecialchars($analyticsQuery) . "</pre>";
    
    $stmt = $pdo->prepare($analyticsQuery);
    $stmt->execute([$userId]);
    $trackedArtists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Query Result:</strong> " . count($trackedArtists) . " artists found</p>";
    
    if (!empty($trackedArtists)) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Event</th><th>Days to Event</th><th>Tracking Status</th><th>Start Date</th></tr>";
        foreach ($trackedArtists as $artist) {
            echo "<tr>";
            echo "<td>{$artist['id']}</td>";
            echo "<td>{$artist['name']}</td>";
            echo "<td>{$artist['event_name']}</td>";
            echo "<td>{$artist['days_to_event']}</td>";
            echo "<td>{$artist['tracking_status']}</td>";
            echo "<td>{$artist['tracking_start_date']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test auto-selection logic
        echo "<h3>Auto-Selection Logic Test:</h3>";
        if (count($trackedArtists) === 1) {
            echo "<p class='success'>✅ Auto-selection SHOULD work: Only 1 artist found, should auto-select ID: {$trackedArtists[0]['id']}</p>";
        } else {
            echo "<p class='warning'>⚠️ Auto-selection will NOT trigger: " . count($trackedArtists) . " artists found</p>";
        }
    } else {
        echo "<p class='error'>❌ NO ARTISTS FOUND - This is the problem!</p>";
    }
    
    echo "<div class='step'><h2>🔬 STEP 3: Detailed Tracking Analysis</h2></div>";
    
    // Check if the tracking info query would work
    echo "<h3>Testing Tracking Info Query (from Analytics.php line 64):</h3>";
    $trackingInfoQuery = "
        SELECT at.id as tracking_id, a.*, at.country_code, at.event_name, at.event_date, 
                at.tracking_start_date, at.tracking_status, at.event_city, at.event_venue
         FROM artists a 
         JOIN artist_trackings at ON a.id = at.artist_id 
         WHERE a.id = ? AND at.user_id = ? AND at.status = 'active'
    ";
    
    echo "<pre>" . htmlspecialchars($trackingInfoQuery) . "</pre>";
    
    $stmt = $pdo->prepare($trackingInfoQuery);
    $stmt->execute([$artistId, $userId]);
    $trackingInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($trackingInfo) {
        echo "<p class='success'>✅ Tracking info found</p>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        foreach ($trackingInfo as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>" . htmlspecialchars($value) . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>❌ No tracking info found for artist ID: $artistId, user ID: $userId</p>";
    }
    
    echo "<div class='step'><h2>🎵 STEP 4: Platform Data Analysis</h2></div>";
    
    // Check platform data
    $platformsData = json_decode($latestTracking['platforms_data'] ?? '{}', true);
    echo "<h3>Platforms Data from Artist Record:</h3>";
    if (empty($platformsData)) {
        echo "<p class='warning'>⚠️ No platforms data found in artist record</p>";
    } else {
        echo "<pre>" . json_encode($platformsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    }
    
    echo "<div class='step'><h2>📈 STEP 5: Metrics Tables Analysis</h2></div>";
    
    $trackingId = $latestTracking['id'];
    
    // Check Spotify metrics
    echo "<h3>Spotify Metrics for Tracking ID: $trackingId</h3>";
    $stmt = $pdo->prepare("SELECT * FROM spotify_metrics WHERE tracking_id = ? ORDER BY metric_date DESC LIMIT 5");
    $stmt->execute([$trackingId]);
    $spotifyMetrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($spotifyMetrics)) {
        echo "<table>";
        echo "<tr><th>Date</th><th>Followers</th><th>Popularity</th><th>Monthly Listeners</th><th>Created</th></tr>";
        foreach ($spotifyMetrics as $metric) {
            echo "<tr>";
            echo "<td>{$metric['metric_date']}</td>";
            echo "<td>{$metric['followers']}</td>";
            echo "<td>{$metric['popularity']}</td>";
            echo "<td>{$metric['monthly_listeners']}</td>";
            echo "<td>{$metric['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ No Spotify metrics found</p>";
    }
    
    // Check Last.fm metrics  
    echo "<h3>Last.fm Metrics for Tracking ID: $trackingId</h3>";
    $stmt = $pdo->prepare("SELECT * FROM lastfm_metrics WHERE tracking_id = ? ORDER BY metric_date DESC LIMIT 5");
    $stmt->execute([$trackingId]);
    $lastfmMetrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($lastfmMetrics)) {
        echo "<table>";
        echo "<tr><th>Date</th><th>Listeners</th><th>Scrobbles</th><th>Created</th></tr>";
        foreach ($lastfmMetrics as $metric) {
            echo "<tr>";
            echo "<td>{$metric['metric_date']}</td>";
            echo "<td>{$metric['listeners']}</td>";
            echo "<td>{$metric['scrobbles']}</td>";
            echo "<td>{$metric['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ No Last.fm metrics found</p>";
    }
    
    echo "<div class='step'><h2>🔧 STEP 6: API Response Simulation</h2></div>";
    
    // Test what the platform manager would return
    echo "<h3>Testing Platform Manager Response:</h3>";
    
    // Include the necessary files
    require_once __DIR__ . '/application/config/database.php';
    require_once __DIR__ . '/application/config/config.php';
    require_once __DIR__ . '/application/libraries/MusicPlatformManager.php';
    
    try {
        $config = include __DIR__ . '/application/config/config.php';
        $platformManager = new MusicPlatformManager($config);
        
        $artistName = $latestTracking['artist_name'];
        echo "<p>Testing search for: <strong>$artistName</strong></p>";
        
        $searchResults = $platformManager->searchArtists($artistName, 'all', 1);
        
        echo "<h4>API Response:</h4>";
        echo "<pre>" . json_encode($searchResults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        
        if (isset($searchResults['combined_results']) && !empty($searchResults['combined_results'])) {
            echo "<p class='success'>✅ API found the artist</p>";
            
            $artistData = $searchResults['combined_results'][0];
            echo "<h4>Artist Data Structure:</h4>";
            echo "<pre>" . json_encode($artistData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        } else {
            echo "<p class='error'>❌ API did not find the artist</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error testing Platform Manager: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
    echo "<div class='step'><h2>🎯 STEP 7: URL Testing</h2></div>";
    
    echo "<h3>URLs to Test:</h3>";
    echo "<ul>";
    echo "<li><a href='/analytics' target='_blank'>Analytics (should auto-select)</a></li>";
    echo "<li><a href='/analytics?artist_id={$artistId}' target='_blank'>Analytics with Artist ID</a></li>";
    echo "<li><a href='/trackings' target='_blank'>Trackings List</a></li>";
    echo "<li><a href='/artists/view/{$artistId}' target='_blank'>Artist View</a></li>";
    echo "</ul>";
    
    echo "<div class='step'><h2>🏁 CONCLUSION</h2></div>";
    
    echo "<h3>Diagnosis Summary:</h3>";
    
    if (empty($trackedArtists)) {
        echo "<p class='error'><strong>PRIMARY ISSUE:</strong> The Analytics query is not finding any tracked artists for user $userId</p>";
        echo "<p><strong>Possible causes:</strong></p>";
        echo "<ul>";
        echo "<li>User session issue (wrong user ID)</li>";
        echo "<li>Tracking status not 'active'</li>";
        echo "<li>Foreign key relationship issue</li>";
        echo "<li>Database inconsistency</li>";
        echo "</ul>";
    } else {
        echo "<p class='success'><strong>QUERY WORKS:</strong> Found " . count($trackedArtists) . " artists</p>";
        if (count($trackedArtists) === 1) {
            echo "<p class='success'><strong>AUTO-SELECT SHOULD WORK</strong></p>";
        }
        
        if (empty($spotifyMetrics) && empty($lastfmMetrics)) {
            echo "<p class='warning'><strong>SECONDARY ISSUE:</strong> No metrics data found</p>";
            echo "<p><strong>Solution:</strong> Run populate metrics</p>";
        } else {
            echo "<p class='success'><strong>METRICS EXIST:</strong> Data should display</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='/analytics'>← Go to Analytics</a> | <a href='/analytics/populateMetrics?populate_key=populate_metrics_2025'>🔧 Populate Metrics</a></p>";
?>
