<?php

$title = "Leaderboard - Click n' Pop";


ob_start();
?>
<div class="leaderboard-container">

    <section class="leaderboard-hero">
        <h1><span>Leaderboard</span></h1>
        <p>Check out the top players and see where you rank!</p>
    </section>

    <section class="leaderboard-table-section">
        <h2>Top Players</h2>

        <table class="leaderboard-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Player</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Sample data for demonstration purposes
                $players = [
                    ['name' => 'Alice', 'score' => 1500],
                    ['name' => 'Bob', 'score' => 1200],
                    ['name' => 'Charlie', 'score' => 1000],
                ];

                foreach ($players as $index => $player) {
                    echo "<tr>";
                    echo "<td>" . ($index + 1) . "</td>";
                    echo "<td>" . htmlspecialchars($player['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($player['score']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </section>
</div>
<?php
$content = ob_get_clean();
include "../templates/layout.php";
?>