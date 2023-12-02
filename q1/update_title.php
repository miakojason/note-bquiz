<?php
include_once "./db.php";
// 檢查是否有名稱為 'id' 的 POST 參數。如果有，代表有一個表單提交，並進行下一步處理。
if(isset($_POST['id'])){
    // 這行程式碼使用 find 方法，根據提交的 'id' 查找相應的數據行。這裡的 $row 變數應該包含了相應的數據行。
    $row=$Title->find($_POST['id']);
    // 這是另一個條件語句，檢查是否有上傳的文件。這裡使用 $_FILES 來處理檔案上傳，img 是表單中文件上傳欄位的名稱。
    if(!empty($_FILES['img']['tmp_name'])){
        // 如果有上傳的文件，這行程式碼將文件移動到指定的目錄，這裡是 './img/' 目錄下，檔名為原始上傳的檔案名稱。
        move_uploaded_file($_FILES['img']['tmp_name'],'./img/'.$_FILES['img']['name']);
         // 將數據行中的 'img' 欄位更新為上傳的檔案名稱。
        $row['img']=$_FILES['img']['name'];
        // 可能是保存修改後的數據行，具體的實現在 $Title 物件的 save 方法中
        $Title->save($row);
    }
}
header("location:index.php");

?>