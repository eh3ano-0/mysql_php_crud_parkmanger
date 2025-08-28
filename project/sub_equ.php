<?php
include("db_connecting.php");

$insert_message = "";
$alter_message = "";
$drop_message = "";
$status = "";

$employes = [];
$sql = "CALL GetEmploye()";
$result = $conn->query($sql);
if ($result) {
    // پردازش نتایج اول
    while ($row = $result->fetch_assoc()) {
        $employes[] = $row;
    }
    // از next_result برای حرکت به کوئری بعدی استفاده کنید
    while ($conn->next_result()) {;}  // پردازش باقی‌مانده نتایج
}

// بررسی ارسال فرم برای افزودن وسیله
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == "add_sub") {
        $datesub = $_POST['datesub'];
        $type = $_POST['type'];
        $model = $_POST['model'];
        $empid = $_POST['empid'];
        $des = $_POST['des'];

        try {
            $stmt = $conn->prepare("CALL AddSub(?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $datesub,$type, $model, $empid, $des);
            if ($stmt->execute()) {
                $insert_message = "اطلاعات وسیله با موفقیت ذخیره شد.";
                $status = "success";
            } else {
                throw new Exception("خطا در ذخیره اطلاعات: " . $conn->error);
            }
        } catch (Exception $e) {
            $insert_message = $e->getMessage();
            $status = "error";
        }
    } elseif ($_POST['action'] == "edit_sub") {
        $id = $_POST['id'];
        $datesub = $_POST['datesub'];
        $type = $_POST['type'];
        $model = $_POST['model'];
        $empid = $_POST['empid'];
        $des = $_POST['des'];

        try {
            $stmt = $conn->prepare("CALL EditSub(?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $id ,$datesub, $type, $model, $empid, $des);
            if ($stmt->execute()) {
                $alter_message = "اطلاعات وسیله با موفقیت ویرایش شد.";
                $status = "success";
            } else {
                throw new Exception("خطا در ویرایش اطلاعات: " . $conn->error);
            }
        } catch (Exception $e) {
            $alter_message = $e->getMessage();
            $status = "error";
        }
    } elseif ($_POST['action'] == "delete_sub") {
        $id = intval($_POST['id']);

        try {
            $stmt = $conn->prepare("CALL DeleteSub(?)");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $drop_message = "اطلاعات وسیله با موفقیت حذف شد.";
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

$subs = [];
$sql = "CALL GetSub()";
$result = $conn->query($sql);
if ($result) {
    // پردازش نتایج اول
    while ($row = $result->fetch_assoc()) {
        $subs[] = $row;
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
    <title>اطلاعات وسیله</title>
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

        <!-- فرم اطلاعات وسیله -->
        <h2>اطلاعات وسیله</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add_sub">
            <label for="datesub" class="form-label">تاریخ ثبت</label>
            <input type="Date" name="datesub" placeholder="تاریخ ثبت" required />

            <select name="type" id="type"  required>
                <option value="" disabled selected>نوع</option>
                <option value="سواری">سواری</option>
                <option value="وانت">وانت</option>
                <option value="ماشین ابزار">ماشین ابزار</option>
                <option value="موتور">موتور</option>
            </select>

            <input type="text" name="model" placeholder="مدل" required />
            
            <select name="empid" required>
                <option value="" disabled selected> انتخاب کارمند</option>
                <?php foreach ($employes as $employe): ?>
                    <option value="<?php echo $employe['EMP_ID']; ?>">
                        <?php echo  " - شناسه: " . $employe['EMP_ID'] . " - موقعیت: " . $employe['position'] . " (حقوق: " . $employe['salary'] . ")". " - تاریخ استخدام: ". $employe['datehir']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <textarea style=" resize: none" id="des" name="des" rows="4" cols="126" placeholder="  توضیحات  " required></textarea>

            <button type="submit">ثبت</button>
        </form>

        <!-- جدول اطلاعات وسیله -->
        <h3>داده‌های وسیله</h3>
        <?php if (count($subs) > 0): ?>   
            <table>
                <thead>
                    <tr>
                        <th>شناسه</th>
                        <th>تاریخ ثبت</th>
                        <th>نوع</th>
                        <th>مدل</th>
                        <th>شناسه کارمند</th>
                        <th>اطلاعات کارمند</th>
                        <th>توضیحات</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subs as $sub): ?>
                        <tr>
                            <td><?php echo $sub['sub_ID']; ?></td>
                            <td><?php echo htmlspecialchars($sub['datesub']); ?></td>
                            <td><?php echo htmlspecialchars($sub['type']); ?></td>
                            <td><?php echo htmlspecialchars($sub['model']); ?></td>
                            <td><?php echo htmlspecialchars($sub['empid']); ?></td>
                            <td><?php echo " موقعیت : " .$sub['position'] . "- حقوق : " . $sub['salary']."- تاریخ استخدام : " . " (" . $sub['datehir'] .")"; ?></td>
                            <td><?php echo htmlspecialchars($sub['des']); ?></td>
                            <td style="display: flex; align-items: center; justify-content: center;">
                                <div>
                                <button class="icon-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($sub)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                </div>
                                <div>
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="delete_sub">
                                    <input type="hidden" name="id" value="<?php echo $sub['sub_ID']; ?>">
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
        <h2>ویرایش وسیله</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="edit_sub">
            <input type="hidden" name="id" id="edit-id">
            <input type="Date" name="datesub" id="edit-datesub" placeholder="تاریخ ثبت" required />

            <select name="type" id="edit-type"  required>
                <option value="" disabled selected>نوع</option>
                <option value="سواری">سواری</option>
                <option value="وانت">وانت</option>
                <option value="ماشین ابزار">ماشین ابزار</option>
                <option value="موتور">موتور</option>
            </select>

            <input type="text" name="model" id="edit-model" placeholder="مدل" required />

            <select name="empid" id="edit-empid" required>
                <option value="" disabled selected> انتخاب کارمند</option>
                <?php foreach ($employes as $employe): ?>
                    <option value="<?php echo $employe['EMP_ID']; ?>">
                        <?php echo  " - شناسه: " . $employe['EMP_ID'] . " - موقعیت: " . $employe['position'] . " (حقوق: " . $employe['salary'] . ")". " - تاریخ استخدام: ". $employe['datehir']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <textarea style=" resize: none" id="edit-des" name="des" rows="4" cols="126" placeholder="  توضیحات  " required></textarea>

            <button type="submit">ذخیره تغییرات</button>
            <button type="button" onclick="closeEditModal()">لغو</button>
        </form>
    </div>

    <div class="footer">درست شده توسط eheano-0 برای درس پایگاه داده</div>

    <script>
        function openEditModal(sub) {
            document.getElementById('edit-id').value = sub.sub_ID;
            document.getElementById('edit-datesub').value = sub.datesub;
            document.getElementById('edit-type').value = sub.type;
            document.getElementById('edit-model').value = sub.model;
            document.getElementById('edit-empid').value = sub.empid;
            document.getElementById('edit-des').value = sub.des;

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
