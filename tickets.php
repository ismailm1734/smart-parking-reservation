<?php
// tickets.php — User Ticket List Page
// Dropdown listing usernames that have at least one ACTIVE ticket.
// After selecting a username, shows all of that user's active tickets.
require_once 'mongo.php';

// ---- Find distinct usernames that have at least one active ticket ----
$command = new MongoDB\Driver\Command([
    'distinct' => 'entries',
    'key'      => 'username',
    'query'    => ['status' => true],
]);
$active_usernames = [];
try {
    $cursor = $manager->executeCommand('tickets', $command);
    $res    = current($cursor->toArray());
    if ($res && isset($res->values)) {
        $active_usernames = $res->values;
    }
} catch (Exception $e) {
    // Collection may not exist yet on first run — that's fine.
}

// ---- If a username was selected, fetch that user's active tickets ----
$selected_user = $_GET['username'] ?? null;
$tickets       = [];

if ($selected_user !== null && $selected_user !== "") {
    $query = new MongoDB\Driver\Query(
        ['username' => $selected_user, 'status' => true],
        ['sort' => ['created_at' => -1]]
    );
    $cursor = $manager->executeQuery('tickets.entries', $query);
    foreach ($cursor as $doc) {
        $tickets[] = $doc;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Support Tickets</title>
</head>
<body>

<a href="index.php">Home</a>

<h2>Support Tickets</h2>

<form method="get">
    <select name="username">
        <option value="">-- Select user --</option>
        <?php foreach ($active_usernames as $u): ?>
            <option value="<?= htmlspecialchars($u) ?>"
                <?= ($u === $selected_user) ? 'selected' : '' ?>>
                <?= htmlspecialchars($u) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Select</button>
</form>

<a href="create_ticket.php">Create a Ticket</a>

<br><br>

<fieldset>
    <legend><b>Results:</b></legend>

    <?php if ($selected_user === null || $selected_user === ""): ?>
        <!-- No user selected yet, results area stays empty -->
    <?php elseif (count($tickets) === 0): ?>
        <p>No active tickets for <b><?= htmlspecialchars($selected_user) ?></b>.</p>
    <?php else: ?>
        <?php foreach ($tickets as $t): ?>
            <fieldset>
                <b>Status:</b> Active<br>
                <b>Body:</b> <?= htmlspecialchars($t->body) ?><br>
                <b>Created At:</b> <?= htmlspecialchars($t->created_at) ?><br>
                <b>Username:</b> <?= htmlspecialchars($t->username) ?><br>
                <a href="ticket_detail.php?id=<?= (string)$t->_id ?>">View Details</a>
            </fieldset>
        <?php endforeach; ?>
    <?php endif; ?>
</fieldset>

</body>
</html>
