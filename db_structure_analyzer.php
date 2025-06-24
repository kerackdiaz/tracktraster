<?php
/**
 * Database Structure Analyzer
 * URL: https://tracktraster.3mas1r.com/db_structure_analyzer.php?debug_key=db_analyze_2025
 */

$debugKey = $_GET['debug_key'] ?? '';
if ($debugKey !== 'db_analyze_2025') {
    die('Access denied. Use ?debug_key=db_analyze_2025');
}

echo "<h1>🗄️ Database Structure Analyzer</h1>";
echo "<style>body { font-family: Arial; margin: 20px; } table { border-collapse: collapse; width: 100%; margin: 10px 0; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; } .table-name { background-color: #e7f3ff; font-weight: bold; }</style>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=tracktraster_db;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✅ Database Connected: tracktraster_db</h2>";
    
    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>📋 Tables Found: " . count($tables) . "</h2>";
    
    foreach ($tables as $table) {
        echo "<h3 class='table-name'>🗂️ Table: $table</h3>";
        
        // Get table structure
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td><strong>{$column['Field']}</strong></td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Get row count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p><strong>Rows:</strong> $count</p>";
        
        // Show sample data for important tables
        if (in_array($table, ['artists', 'artist_trackings', 'spotify_metrics', 'lastfm_metrics', 'users']) && $count > 0) {
            echo "<h4>📊 Sample Data (Last 3 rows):</h4>";
            $stmt = $pdo->query("SELECT * FROM $table ORDER BY id DESC LIMIT 3");
            $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($samples)) {
                echo "<table>";
                // Headers
                echo "<tr>";
                foreach (array_keys($samples[0]) as $key) {
                    echo "<th>$key</th>";
                }
                echo "</tr>";
                
                // Data
                foreach ($samples as $sample) {
                    echo "<tr>";
                    foreach ($sample as $value) {
                        $displayValue = is_null($value) ? '<em>NULL</em>' : htmlspecialchars(substr($value, 0, 50));
                        if (strlen($value) > 50) $displayValue .= '...';
                        echo "<td>$displayValue</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        
        echo "<hr>";
    }
    
    // Specific queries for tracking analysis
    echo "<h2>🔍 Tracking Analysis</h2>";
    
    // Check tracking with details
    echo "<h3>🎯 Current Trackings with Full Details:</h3>";
    $stmt = $pdo->query("
        SELECT 
            at.id as tracking_id,
            at.user_id,
            at.artist_id,
            a.name as artist_name,
            a.spotify_id,
            at.country_code,
            at.event_name,
            at.event_date,
            at.tracking_start_date,
            at.tracking_status,
            at.status,
            at.created_at,
            at.updated_at,
            (SELECT COUNT(*) FROM spotify_metrics sm WHERE sm.tracking_id = at.id) as spotify_metrics_count,
            (SELECT COUNT(*) FROM lastfm_metrics lm WHERE lm.tracking_id = at.id) as lastfm_metrics_count
        FROM artist_trackings at
        JOIN artists a ON at.artist_id = a.id
        WHERE at.status = 'active'
        ORDER BY at.created_at DESC
    ");
    
    $trackings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($trackings)) {
        echo "<table>";
        echo "<tr><th>Tracking ID</th><th>User</th><th>Artist</th><th>Spotify ID</th><th>Event</th><th>Country</th><th>Start Date</th><th>Status</th><th>Spotify Metrics</th><th>LastFM Metrics</th></tr>";
        foreach ($trackings as $tracking) {
            echo "<tr>";
            echo "<td>{$tracking['tracking_id']}</td>";
            echo "<td>{$tracking['user_id']}</td>";
            echo "<td>{$tracking['artist_name']}</td>";
            echo "<td>{$tracking['spotify_id']}</td>";
            echo "<td>{$tracking['event_name']}</td>";
            echo "<td>{$tracking['country_code']}</td>";
            echo "<td>{$tracking['tracking_start_date']}</td>";
            echo "<td>{$tracking['tracking_status']}</td>";
            echo "<td style='text-align: center;'>{$tracking['spotify_metrics_count']}</td>";
            echo "<td style='text-align: center;'>{$tracking['lastfm_metrics_count']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check metrics details for the latest tracking
        $latestTracking = $trackings[0];
        echo "<h3>🔬 Metrics Details for Tracking ID: {$latestTracking['tracking_id']}</h3>";
        
        // Spotify metrics
        echo "<h4>🎵 Spotify Metrics:</h4>";
        $stmt = $pdo->prepare("SELECT * FROM spotify_metrics WHERE tracking_id = ? ORDER BY date_recorded DESC LIMIT 5");
        $stmt->execute([$latestTracking['tracking_id']]);
        $spotifyMetrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($spotifyMetrics)) {
            echo "<table>";
            echo "<tr><th>Date</th><th>Followers</th><th>Popularity</th><th>Monthly Listeners</th><th>Created</th></tr>";
            foreach ($spotifyMetrics as $metric) {
                echo "<tr>";
                echo "<td>{$metric['date_recorded']}</td>";
                echo "<td>{$metric['followers']}</td>";
                echo "<td>{$metric['popularity']}</td>";
                echo "<td>{$metric['monthly_listeners']}</td>";
                echo "<td>{$metric['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No Spotify metrics found</p>";
        }
        
        // LastFM metrics
        echo "<h4>🎧 Last.fm Metrics:</h4>";
        $stmt = $pdo->prepare("SELECT * FROM lastfm_metrics WHERE tracking_id = ? ORDER BY date_recorded DESC LIMIT 5");
        $stmt->execute([$latestTracking['tracking_id']]);
        $lastfmMetrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($lastfmMetrics)) {
            echo "<table>";
            echo "<tr><th>Date</th><th>Listeners</th><th>Playcount</th><th>Created</th></tr>";
            foreach ($lastfmMetrics as $metric) {
                echo "<tr>";
                echo "<td>{$metric['date_recorded']}</td>";
                echo "<td>{$metric['listeners']}</td>";
                echo "<td>{$metric['playcount']}</td>";
                echo "<td>{$metric['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No Last.fm metrics found</p>";
        }
    } else {
        echo "<p>No active trackings found</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='/analytics'>← Go to Analytics</a></p>";
?>
