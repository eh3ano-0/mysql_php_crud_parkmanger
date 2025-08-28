<?php
include("db_connecting.php");

$insert_message = "";
$alter_message = "";
$drop_message = "";
$status = "";

$subs = [];
$sql = "CALL GetSub_use()";
$result = $conn->query($sql);
if ($result) {
    // پردازش نتایج اول
    while ($row = $result->fetch_assoc()) {
        $subs[] = $row;
    }
    // از next_result برای حرکت به کوئری بعدی استفاده کنید
    while ($conn->next_result()) {;}  // پردازش باقی‌مانده نتایج
}

$drivers = [];
$sql = "CALL GetDriver_use()";
$result = $conn->query($sql);
if ($result) {
    // پردازش نتایج اول
    while ($row = $result->fetch_assoc()) {
        $drivers[] = $row;
    }
    // از next_result برای حرکت به کوئری بعدی استفاده کنید
    while ($conn->next_result()) {;}  // پردازش باقی‌مانده نتایج
}


// بررسی ارسال فرم برای افزودن گزارش
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == "add_use") {
        $dateuseaz = $_POST['dateuseaz'];
        $dateuseta = $_POST['dateuseta'];
        $driverid = $_POST['driverid'];
        $subid = $_POST['subid'];
        $desc = $_POST['desc'];

        try {
            $stmt = $conn->prepare("CALL AddUse(?, ?, ?, ?, ?)");
            $stmt->bind_param("sssis", $dateuseaz,$dateuseta, $driverid, $subid, $desc);
            if ($stmt->execute()) {
                $insert_message = "اطلاعات گزارش با موفقیت ذخیره شد.";
                $status = "success";
            } else {
                throw new Exception("خطا در ذخیره اطلاعات: " . $conn->error);
            }
        } catch (Exception $e) {
            $insert_message = $e->getMessage();
            $status = "error";
        }
    } elseif ($_POST['action'] == "edit_use") {
        $id = $_POST['id'];
        $dateuseaz = $_POST['dateuseaz'];
        $dateuseta = $_POST['dateuseta'];
        $driverid = $_POST['driverid'];
        $subid = $_POST['subid'];
        $desc = $_POST['desc'];

        try {
            $stmt = $conn->prepare("CALL EditUse(?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssis", $id ,$dateuseaz,$dateuseta, $driverid, $subid, $desc);
            if ($stmt->execute()) {
                $alter_message = "اطلاعات گزارش با موفقیت ویرایش شد.";
                $status = "success";
            } else {
                throw new Exception("خطا در ویرایش اطلاعات: " . $conn->error);
            }
        } catch (Exception $e) {
            $alter_message = $e->getMessage();
            $status = "error";
        }
    } elseif ($_POST['action'] == "delete_use") {
        $id = intval($_POST['id']);

        try {
            $stmt = $conn->prepare("CALL DeleteUse(?)");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $drop_message = "اطلاعات گزارش با موفقیت حذف شد.";
                $status = "success";
            } else {
                throw new Exception("خطا در حذف اطلاعات: " . $conn->error);
            }
        } catch (Exception $e) {
            $drop_message = $e->getMessage();
            $status = "error";
        }
    }
}

$uses = [];
$sql = "CALL GetUse()";
$result = $conn->query($sql);
if ($result) {
    // پردازش نتایج اول
    while ($row = $result->fetch_assoc()) {
        $uses[] = $row;
    }
    // از next_result برای حرکت به کوئری بعدی استفاده کنید
    while ($conn->next_result()) {;}  // پردازش باقی‌مانده نتایج
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اطلاعات گزارش</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
            direction: rtl;
        }
    
        body {
            background: conic-gradient(
                from 240deg at 50% 50%,
                #00ffc3,
                #00fad9,
                #00f4f0,
                #00eeff,
                #00e6ff,
                #00dcff,
                #00d2ff,
                #00c5ff,
                #00b8ff,
                #6da8ff,
                #9f97ff,
                #c285ff
              );
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
    
        .container {
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            width: 100%;
        }
    
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    
    
        input[type="text"], input[type="number"], input[type="date"], select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        
        input[type="currency"],select {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            margin-left: 25px;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            background: #333;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 15px;
            margin-bottom: 10px;
        }
    
        button:hover {
            background: #676768;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            text-align: center;
            padding: 8px;
        }

        th {
            background-color: #f4f4f4;
        }
    
        .footer {
            position: fixed;
            bottom: 10px;
            right: 10px;
            font-size: 12px;
            color: #777;
            font-style: italic;
        }

        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #333;
        }

        .icon-btn i {
            font-size: 18px;
        }

        .icon-btn i:hover {
            color: #555;
        }

        .alert {
            margin: 10px 0;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            text-align: center;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-close {
            float: right;
            font-size: 20px;
            line-height: 20px;
            cursor: pointer;
            color: inherit;
        }

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            border-radius: 8px;
        }

        .modal.active {
            display: block;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .modal-overlay.active {
            display: block;
        }

        .home-button {
            position: fixed;
            top: 20px;
            left: 20px;
            width: 60px;
            height: 60px;
            background-color:rgb(97, 255, 110);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .home-button i {
            color: #fff;
            font-size: 24px;
            transition: transform 0.2s ease-in-out;
        }

        .home-button:hover {
            transform: scale(1.1);
            box-shadow: 0 10px 25px rgba(82, 185, 13, 0.5);
        }

        .home-button:hover i {
            transform: rotate(20deg) scale(1.1);
        }

    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <a href="http://127.0.0.1/reza/main.html?open=true" class="home-button" title="Home">
        <i class="fas fa-home"></i>
    </a>


    <div class="container">
        <!-- نمایش پیام‌ها -->
        <?php if (!empty($insert_message)): ?>
            <div class="alert <?php echo $status === 'success' ? 'alert-success' : 'alert-danger'; ?>">
                <span class="alert-close" onclick="this.parentElement.style.display='none';">&times;</span>
                <?php echo $insert_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($alter_message)): ?>
            <div class="alert <?php echo $status === 'success' ? 'alert-success' : 'alert-danger'; ?>">
                <span class="alert-close" onclick="this.parentElement.style.display='none';">&times;</span>
                <?php echo $alter_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($drop_message)): ?>
            <div class="alert <?php echo $status === 'success' ? 'alert-success' : 'alert-danger'; ?>">
                <span class="alert-close" onclick="this.parentElement.style.display='none';">&times;</span>
                <?php echo $drop_message; ?>
            </div>
        <?php endif; ?>

        <!-- فرم اطلاعات گزارش -->
        <h2>اطلاعات گزارش</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add_use">
            <label for="dateuseaz" class="form-label">از تاریخ</label>
            <input type="Date" name="dateuseaz" placeholder="از تاریخ" required />

            <label for="dateuseta" class="form-label">تا تاریخ</label>
            <input type="Date" name="dateuseta" placeholder="تا تاریخ" required />
            
            <select name="driverid" required>
                <option value="" disabled selected> انتخاب راننده</option>
                <?php foreach ($drivers as $driver): ?>
                    <option value="<?php echo $driver['dr_ID']; ?>">
                        <?php echo  " - شناسه: " . $driver['dr_ID'] . " - سطح: " . $driver['level'] . " (شماره گواهینامه: " . $driver['idlic'] . ")". " - پایه : ". $driver['paye']. " - آدرس : ". $driver['address']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="subid" required>
                <option value="" disabled selected> انتخاب وسیله</option>
                <?php foreach ($subs as $sub): ?>
                    <option value="<?php echo $sub['sub_ID']; ?>">
                        <?php echo  " - شناسه: " . $sub['sub_ID'] . " - تاریخ ثبت : " . $sub['datesub'] ."-".$sub['type']."-".$sub['model']."- توضیحات :".$sub['des']."- شناسه کارمند :".$sub['empid']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <textarea style=" resize: none" id="desc" name="desc" rows="4" cols="126" placeholder="  توضیحات  " required></textarea>

            <button type="submit">ثبت</button>
        </form>

        <!-- جدول اطلاعات گزارش -->
        <h3>داده‌های گزارش</h3>
        <?php if (count($uses) > 0): ?>   
            <table>
                <thead>
                    <tr>
                        <th>شناسه</th>
                        <th> از تاریخ</th>
                        <th> تا تاریخ</th>
                        <th>مشخصات راننده</th>
                        <th>مشخصات وسیله</th>
                        <th>توضیحات</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($uses as $use): ?>
                        <tr>
                            <td><?php echo $use['use_ID']; ?></td>
                            <td><?php echo htmlspecialchars($use['dateuseaz']); ?></td>
                            <td><?php echo htmlspecialchars($use['dateuseta']); ?></td>
                            <td><?php echo " - شناسه: " . $use['driverid'] . " - سطح: " . $use['level'] . " (شماره گواهینامه: " . $use['idlic'] . ")". " - پایه : ". $use['paye']. " - آدرس : ". $use['address']; ?></td>
                            <td><?php echo " - شناسه: " . $use['subid'] . " - تاریخ ثبت : " . $use['datesub'] ."-".$use['type']."-".$use['model']."- شناسه کارمند :".$use['empid']."-  توضیحات وسیله :".$use['des']; ?></td>
                            <td><?php echo htmlspecialchars($use['desc']); ?></td>
                            <td style="display: flex; align-items: center; justify-content: center;">
                                <div>
                                <button class="icon-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($use)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                </div>
                                <div>
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="delete_use">
                                    <input type="hidden" name="id" value="<?php echo $use['use_ID']; ?>">
                                    <button class="icon-btn" onclick="return confirm('آیا مطمئن هستید؟')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>        
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-danger">هیچ داده‌ای موجود نیست.</div>
        <?php endif; ?>    
    </div>

    <!-- فرم پاپ‌آپ ویرایش -->
    <div class="modal-overlay" id="modal-overlay"></div>
    <div class="modal" id="edit-modal">
        <h2>ویرایش گزارش</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="edit_use">
            <input type="hidden" name="id" id="edit-id">
            
            <input type="Date" name="dateuseaz" id="edit-dateuseaz" placeholder="از تاریخ" required />
            <input type="Date" name="dateuseta" id="edit-dateuseta" placeholder="تا تاریخ" required />
            
            <select name="driverid" id="edit-driverid" required>
                <option value="" disabled selected> انتخاب راننده</option>
                <?php foreach ($drivers as $driver): ?>
                    <option value="<?php echo $driver['dr_ID']; ?>">
                        <?php echo  " - شناسه: " . $driver['dr_ID'] . " - سطح: " . $driver['level'] . " (شماره گواهینامه: " . $driver['idlic'] . ")". " - پایه : ". $driver['paye']. " - آدرس : ". $driver['address']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="subid" id="edit-subid" required>
                <option value="" disabled selected> انتخاب وسیله</option>
                <?php foreach ($subs as $sub): ?>
                    <option value="<?php echo $sub['sub_ID']; ?>">
                        <?php echo  " - شناسه: " . $sub['sub_ID'] . " - تاریخ ثبت : " . $sub['datesub'] ."-".$sub['type']."-".$sub['model']."- توضیحات :".$sub['des']."- شناسه کارمند :".$sub['empid']; ?>
                    </option>
                <?php endforeach; ?>
            </select>


            <textarea style=" resize: none" id="edit-desc" name="desc" rows="4" cols="126" placeholder="  توضیحات  " required></textarea>

            <button type="submit">ذخیره تغییرات</button>
            <button type="button" onclick="closeEditModal()">لغو</button>
        </form>
    </div>

    <div class="footer">درست شده توسط eheano-0 برای درس پایگاه داده</div>

    <script>
        function openEditModal(use) {
            document.getElementById('edit-id').value = use.use_ID;
            document.getElementById('edit-dateuseaz').value = use.dateuseaz;
            document.getElementById('edit-dateuseta').value = use.dateuseta;
            document.getElementById('edit-driverid').value = use.driverid;
            document.getElementById('edit-subid').value = use.subid;
            document.getElementById('edit-desc').value = use.desc;

            document.getElementById('edit-modal').classList.add('active');
            document.getElementById('modal-overlay').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('edit-modal').classList.remove('active');
            document.getElementById('modal-overlay').classList.remove('active');
        }

    </script>
    
</body>
</html>
