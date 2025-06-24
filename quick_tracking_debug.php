<?php
/**
 * Quick Diagnostic for New Tracking Issue
 * Uso: https://tracktraster.3mas1r.com/quick_tracking_debug.php?debug_key=track_debug_2025
 */

// Security check
$debugKey = $_GET['debug_key'] ?? '';
if ($debugKey !== 'track_debug_2025') {
    die('Access denied. Use ?debug_key=track_debug_2025');
}

echo "<h1>🔍 Quick Tracking Debug</h1>";
echo "<style>body { font-family: Arial; margin: 20px; } table { border-collapse: collapse; width: 100%; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; }</style>";

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'tracktraster_db';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✅ Database Connected</h2>";
    
    // Check recent trackings
    echo "<h2>📊 Recent Trackings (Last 24 hours)</h2>";
    $stmt = $pdo->query("
        SELECT at.id, at.user_id, a.name as artist_name, at.event_name, 
               at.tracking_start_date, at.status, at.created_at,
               (SELECT COUNT(*) FROM spotify_metrics sm WHERE sm.tracking_id = at.id) as spotify_count,
               (SELECT COUNT(*) FROM lastfm_metrics lm WHERE lm.tracking_id = at.id) as lastfm_count
        FROM artist_trackings at 
        JOIN artists a ON at.artist_id = a.id 
        WHERE at.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY at.created_at DESC
    ");
    
    $recentTrackings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($recentTrackings)) {
        echo "<p style='color: orange;'>⚠️ No hay trackings creados en las últimas 24 horas</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Usuario</th><th>Artista</th><th>Evento</th><th>Creado</th><th>Estado</th><th>Spotify Metrics</th><th>LastFM Metrics</th></tr>";
        foreach ($recentTrackings as $tracking) {
            echo "<tr>";
            echo "<td>{$tracking['id']}</td>";
            echo "<td>{$tracking['user_id']}</td>";
            echo "<td>{$tracking['artist_name']}</td>";
            echo "<td>{$tracking['event_name']}</td>";
            echo "<td>{$tracking['created_at']}</td>";
            echo "<td>{$tracking['status']}</td>";
            echo "<td style='text-align: center;'>{$tracking['spotify_count']}</td>";
            echo "<td style='text-align: center;'>{$tracking['lastfm_count']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check users
    echo "<h2>👥 Users</h2>";
    $stmt = $pdo->query("SELECT id, username, full_name, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Created</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['full_name']}</td>";
        echo "<td>{$user['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check artists
    echo "<h2>🎵 Recent Artists</h2>";
    $stmt = $pdo->query("SELECT id, name, spotify_id, image_url, created_at FROM artists ORDER BY created_at DESC LIMIT 5");
    $artists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Spotify ID</th><th>Image</th><th>Created</th></tr>";
    foreach ($artists as $artist) {
        echo "<tr>";
        echo "<td>{$artist['id']}</td>";
        echo "<td>{$artist['name']}</td>";
        echo "<td>{$artist['spotify_id']}</td>";
        echo "<td>" . ($artist['image_url'] ? '✅' : '❌') . "</td>";
        echo "<td>{$artist['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test Analytics Query
    echo "<h2>🔍 Analytics Query Test</h2>";
    if (!empty($recentTrackings)) {
        $latestTracking = $recentTrackings[0];
        $userId = $latestTracking['user_id'];
        
        echo "<p><strong>Testing for User ID:</strong> $userId</p>";
        
        $stmt = $pdo->prepare("
            SELECT DISTINCT a.id, a.name, a.image_url, at.event_name, at.event_date,
                    at.tracking_status, at.tracking_start_date,
                    DATEDIFF(COALESCE(at.event_date, DATE_ADD(CURDATE(), INTERVAL 30 DAY)), CURDATE()) as days_to_event
             FROM artists a 
             JOIN artist_trackings at ON a.id = at.artist_id 
             WHERE at.user_id = ? AND at.status = 'active'
             ORDER BY at.event_date ASC, a.name
        ");
        $stmt->execute([$userId]);
        $trackedArtists = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Tracked Artists for User $userId:</strong> " . count($trackedArtists) . "</p>";
        
        if (!empty($trackedArtists)) {
            echo "<table>";
            echo "<tr><th>Artist ID</th><th>Name</th><th>Event</th><th>Days to Event</th><th>Status</th></tr>";
            foreach ($trackedArtists as $artist) {
                echo "<tr>";
                echo "<td>{$artist['id']}</td>";
                echo "<td>{$artist['name']}</td>";
                echo "<td>{$artist['event_name']}</td>";
                echo "<td>{$artist['days_to_event']}</td>";
                echo "<td>{$artist['tracking_status']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Try auto-select logic
            if (count($trackedArtists) === 1) {
                echo "<p style='color: green;'>✅ Auto-select should work: Only 1 artist found</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Multiple artists found, auto-select won't trigger</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ No tracked artists found for this user</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='/analytics'>← Go to Analytics</a></p>";
echo "<p><a href='/analytics/populateMetrics?populate_key=populate_metrics_2025'>🔧 Populate Metrics</a></p>";
?>
