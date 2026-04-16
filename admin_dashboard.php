<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

require 'db.php';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = (int) $_POST['id'];
    if ($_POST['action'] === 'approve') {
        // Move to scholarships table
        $stmt = $conn->prepare("SELECT * FROM suggested_scholarships WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();

        if ($data) {
            $insert = $conn->prepare("INSERT INTO scholarships (name, provider, amount, stream, deadline, description, apply_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert->bind_param("sssssss", $data['name'], $data['provider'], $data['amount'], $data['stream'], $data['deadline'], $data['description'], $data['apply_url']);
            $insert->execute();

            // Update stats
            $conn->query("UPDATE site_stats SET metric_value = metric_value + 1 WHERE metric_name = 'total_scholarships'");

            // Mark as approved
            $update = $conn->prepare("UPDATE suggested_scholarships SET status = 'approved' WHERE id = ?");
            $update->bind_param("i", $id);
            $update->execute();
        }
    } else if ($_POST['action'] === 'reject') {
        $update = $conn->prepare("UPDATE suggested_scholarships SET status = 'rejected' WHERE id = ?");
        $update->bind_param("i", $id);
        $update->execute();
    }
    header("Location: admin_dashboard.php");
    exit;
}

// Handle ad approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ad_action'])) {
    $ad_id = (int) $_POST['ad_id'];
    if ($_POST['ad_action'] === 'approve_ad') {
        $update = $conn->prepare("UPDATE ads SET status = 'approved' WHERE id = ?");
        $update->bind_param("i", $ad_id);
        $update->execute();
    } else if ($_POST['ad_action'] === 'reject_ad') {
        $update = $conn->prepare("UPDATE ads SET status = 'rejected' WHERE id = ?");
        $update->bind_param("i", $ad_id);
        $update->execute();
    }
    header("Location: admin_dashboard.php");
    exit;
}

// Handle deletion of active scholarships
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_scholarship_id'])) {
    $del_id = (int) $_POST['delete_scholarship_id'];
    $stmt = $conn->prepare("DELETE FROM scholarships WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}

// Handle deletion of active ads
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ad_id'])) {
    $del_ad_id = (int) $_POST['delete_ad_id'];
    $stmt = $conn->prepare("DELETE FROM ads WHERE id = ?");
    $stmt->bind_param("i", $del_ad_id);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}

// Handle direct scholarship addition by admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_scholarship'])) {
    $name = $_POST['name'] ?? '';
    $provider = $_POST['provider'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $stream = $_POST['stream'] ?? '';
    $deadline = $_POST['deadline'] ?? '';
    $description = $_POST['description'] ?? '';
    $apply_url = $_POST['apply_url'] ?? '';

    if ($name && $provider && $stream && $deadline) {
        $insert = $conn->prepare("INSERT INTO scholarships (name, provider, amount, stream, deadline, description, apply_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param("sssssss", $name, $provider, $amount, $stream, $deadline, $description, $apply_url);
        $insert->execute();

        // Update stats
        $conn->query("UPDATE site_stats SET metric_value = metric_value + 1 WHERE metric_name = 'total_scholarships'");
    }
    header("Location: admin_dashboard.php");
    exit;
}

$result = $conn->query("SELECT * FROM suggested_scholarships WHERE status = 'pending' ORDER BY created_at DESC");
$ad_result = $conn->query("SELECT * FROM ads WHERE status = 'pending' ORDER BY created_at DESC");
$active_scholarships = $conn->query("SELECT * FROM scholarships ORDER BY id DESC");
$active_ads = $conn->query("SELECT * FROM ads WHERE status = 'approved' ORDER BY start_date DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container {
            padding: 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: var(--primary);
            color: white;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-weight: 600;
            margin-right: 5px;
        }

        .approve {
            background: var(--accent);
        }

        .reject {
            background: var(--danger);
        }

        .logout {
            float: right;
            padding: 0.5rem 1rem;
            background: var(--danger);
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .admin-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-top: 1rem;
            margin-bottom: 2rem;
        }
        .admin-form .form-group { margin-bottom: 1rem; }
        .admin-form label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
        .admin-form input, .admin-form select, .admin-form textarea { width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        .admin-form button { padding: 0.8rem 1.5rem; border: none; background: var(--primary); color: white; border-radius: 6px; cursor: pointer; font-weight: bold; }
    </style>
</head>

<body style="background: #f1f5f9;">
    <div class="dashboard-container">
        <a href="admin_logout.php" class="logout">Logout</a>
        <h2>Welcome to Admin Dashboard</h2>
        <p>Review new scholarship suggestions from students or directly add new ones.</p>

        <div class="admin-form">
            <h3>Add New Scholarship Directly</h3>
            <form method="POST">
                <input type="hidden" name="add_scholarship" value="1">
                <div class="form-group">
                    <label>Scholarship Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Provider</label>
                    <input type="text" name="provider" required>
                </div>
                <div class="form-group">
                    <label>Amount / Benefit</label>
                    <input type="text" name="amount" placeholder="e.g. Up to ₹50,000">
                </div>
                <div class="form-group">
                    <label>Eligible Stream</label>
                    <select name="stream" required>
                        <option value="All">All Streams</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Medical">Medical</option>
                        <option value="Arts">Arts</option>
                        <option value="Commerce">Commerce</option>
                        <option value="Science">Science</option>
                        <option value="School">School</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Deadline</label>
                    <input type="date" name="deadline" required>
                </div>
                <div class="form-group">
                    <label>Description (Optional)</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Application URL</label>
                    <input type="url" name="apply_url" placeholder="https://" required>
                </div>
                <button type="submit">Submit Scholarship</button>
            </form>
        </div>

        <h2 style="margin-top: 3rem;">Pending Suggestions</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Provider</th>
                    <th>Amount</th>
                    <th>Stream</th>
                    <th>Deadline</th>
                    <th>Link</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['provider']) ?></td>
                        <td><?= htmlspecialchars($row['amount']) ?></td>
                        <td><?= htmlspecialchars($row['stream']) ?></td>
                        <td><?= htmlspecialchars($row['deadline']) ?></td>
                        <td><a href="<?= htmlspecialchars($row['apply_url']) ?>" target="_blank">View Link</a></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" name="action" value="approve"
                                    class="action-btn approve">Approve</button>
                                <button type="submit" name="action" value="reject" class="action-btn reject">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows === 0): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">No pending suggestions.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h2 style="margin-top: 3rem;">Pending Advertisements</h2>
        <p>Review and approve new ad banners.</p>
        <table>
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Target URL</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Banner</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($ad_row = $ad_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($ad_row['company_name']) ?></td>
                        <td><a href="<?= htmlspecialchars($ad_row['target_url']) ?>" target="_blank">Target URL</a></td>
                        <td><?= htmlspecialchars($ad_row['start_date']) ?></td>
                        <td><?= htmlspecialchars($ad_row['end_date']) ?></td>
                        <td><a href="<?= htmlspecialchars($ad_row['banner_image']) ?>" target="_blank">View Image</a></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="ad_id" value="<?= $ad_row['id'] ?>">
                                <button type="submit" name="ad_action" value="approve_ad"
                                    class="action-btn approve">Approve</button>
                                <button type="submit" name="ad_action" value="reject_ad" class="action-btn reject">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($ad_result->num_rows === 0): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No pending ad requests.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h2 style="margin-top: 3rem;">Manage Active Scholarships</h2>
        <p>Review and manually remove currently active scholarships from the portal.</p>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Provider</th>
                    <th>Stream</th>
                    <th>Deadline</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($s_row = $active_scholarships->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($s_row['name']) ?></td>
                        <td><?= htmlspecialchars($s_row['provider']) ?></td>
                        <td><?= htmlspecialchars($s_row['stream']) ?></td>
                        <td><?= htmlspecialchars($s_row['deadline']) ?></td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to remove this scholarship?');">
                                <input type="hidden" name="delete_scholarship_id" value="<?= $s_row['id'] ?>">
                                <button type="submit" class="action-btn reject">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($active_scholarships->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No active scholarships.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h2 style="margin-top: 3rem;">Manage Active Advertisements</h2>
        <p>Review and manually remove currently active or approved ad banners.</p>
        <table>
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Target URL</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($a_row = $active_ads->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($a_row['company_name']) ?></td>
                        <td><a href="<?= htmlspecialchars($a_row['target_url']) ?>" target="_blank">Target URL</a></td>
                        <td><?= htmlspecialchars($a_row['start_date']) ?></td>
                        <td><?= htmlspecialchars($a_row['end_date']) ?></td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to totally remove this ad?');">
                                <input type="hidden" name="delete_ad_id" value="<?= $a_row['id'] ?>">
                                <button type="submit" class="action-btn reject">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($active_ads->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No active ads.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>