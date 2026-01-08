<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

include "config/koneksi.php";
$username = $_SESSION['username'];

// Logika simpan data
if (isset($_POST['add_device'])) {
    $broker = mysqli_real_escape_string($koneksi, $_POST['broker_url']);
    $user   = mysqli_real_escape_string($koneksi, $_POST['mq_user']);
    $pass   = mysqli_real_escape_string($koneksi, $_POST['mq_pass']);
    $type   = mysqli_real_escape_string($koneksi, $_POST['device_type']);

    $query = "INSERT INTO device (broker_url, mq_user, mq_pass, device_type) 
              VALUES ('$broker', '$user', '$pass', '$type')";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Device Berhasil Ditambahkan!'); window.location='dashboard.php';</script>";
        exit;
    }
}

// Fetch devices from database
$devices = mysqli_query($koneksi, "SELECT * FROM device ORDER BY device_id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Device</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f8;
            margin: 0;
            min-height: 100vh;
            padding-bottom: 40px;
        }

        /* Navigasi Pojok Kiri Atas */
        .top-left {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 100;
        }

        /* Navigasi Pojok Kanan Atas */
        .top-right {
            position: fixed;
            top: 25px;
            right: 20px;
            z-index: 100;
            background: rgba(255,255,255,0.95);
            padding: 8px 16px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-new {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .btn-new:hover { background: #218838; transform: translateY(-2px); }

        .logout {
            color: #ff4d4d;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        .logout:hover { text-decoration: underline; }

        /* Modal Pop-up */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
        }
        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 25px;
            width: 320px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .modal-content input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
        }
        .btn-save {
            width: 100%;
            padding: 12px;
            background: #66a6ff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Device Cards Container */
        .devices-container {
            max-width: 1200px;
            margin: 100px auto 0;
            padding: 0 20px;
        }
        
        .devices-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 24px;
        }

        .devices-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .device-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
            border: 2px solid transparent;
        }
        
        .device-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            border-color: #66a6ff;
        }

        .device-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 24px;
        }

        .device-icon.incubator {
            background: linear-gradient(135deg, #FFF8EC, #FFE4B5);
        }

        .device-icon.smartlamp {
            background: linear-gradient(135deg, #E8F5E9, #C8E6C9);
        }

        .device-icon.default {
            background: linear-gradient(135deg, #E3F2FD, #BBDEFB);
        }

        .device-type {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .device-broker {
            font-size: 13px;
            color: #888;
            word-break: break-all;
        }

        .device-status {
            display: inline-block;
            margin-top: 12px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .device-status.ready {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body>

<div class="top-left">
    <button class="btn-new" onclick="openModal()">+ New Device</button>
</div>

<div class="top-right">
    <span style="margin-right: 15px; color: #555;">Halo, <b><?= htmlspecialchars($username) ?></b></span>
    <a href="logout.php" class="logout">Logout</a>
</div>

<!-- Device Cards Section -->
<div class="devices-container">
    <h2 class="devices-title">🔌 My Devices</h2>
    
    <?php if (mysqli_num_rows($devices) > 0): ?>
    <div class="devices-grid">
        <?php while ($device = mysqli_fetch_assoc($devices)): ?>
            <?php
            // Determine device link and icon based on type
            $deviceType = $device['device_type'];
            $link = '#';
            $icon = '📟';
            $iconClass = 'default';
            
            if (strpos($deviceType, 'inkubator') !== false || strpos($deviceType, 'incubator') !== false) {
                $link = 'iot-dashboard/incubator32/incubator_dashboard.php';
                $icon = '🥚';
                $iconClass = 'incubator';
            } elseif (strpos($deviceType, 'smartlamp') !== false || strpos($deviceType, 'lamp') !== false) {
                $link = '#'; // Can be updated when smartlamp dashboard is created
                $icon = '💡';
                $iconClass = 'smartlamp';
            }
            ?>
            <a href="<?= $link ?>" class="device-card">
                <div class="device-icon <?= $iconClass ?>">
                    <?= $icon ?>
                </div>
                <div class="device-type"><?= htmlspecialchars($device['device_type']) ?></div>
                <div class="device-broker">
                    <?= !empty($device['broker_url']) ? htmlspecialchars($device['broker_url']) : 'No broker configured' ?>
                </div>
                <span class="device-status ready">Ready</span>
            </a>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
        </svg>
        <p style="font-size: 18px; margin-bottom: 8px;">Belum ada device</p>
        <p>Klik tombol "+ New Device" untuk menambahkan device pertama Anda.</p>
    </div>
    <?php endif; ?>
</div>

<div id="deviceModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-top:0; color: #333;">Add New Device</h3>
        <form method="post">
            <label style="font-size: 13px; color: gray;">Pilih Tipe Device:</label>
            <select name="device_type" required style="width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ddd; border-radius: 6px; background: white;">
                <option value="" disabled selected>-- Pilih Tipe --</option>
                <option value="esp32-inkubator">esp32-inkubator</option>
                <option value="esp32-smartlamp">esp32-smartlamp </option>
                <option value="coming-soon">coming-soon</option>
            </select>

            <input type="text" name="broker_url" placeholder="Broker URL" required>
            
            <input type="text" name="mq_user" placeholder="MQ User ">
            <input type="password" name="mq_pass" placeholder="MQ Password ">
            
            <button type="submit" name="add_device" class="btn-save">Simpan Device</button>
            <button type="button" onclick="closeModal()" style="width:100%; background:none; border:none; color:gray; cursor:pointer; margin-top:10px;">Batal</button>
        </form>
    </div>
</div>
<script>
    function openModal() { document.getElementById('deviceModal').style.display = 'block'; }
    function closeModal() { document.getElementById('deviceModal').style.display = 'none'; }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('deviceModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
</script>

</body>
</html>