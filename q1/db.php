<?php
date_default_timezone_set("Asia/Taipei");
session_start();
class DB{
    protected $dsn = "mysql:host=localhost;charset=utf8;dbname=bquiz";
    protected $pdo;
    protected $table;
    // -------------------- __construct-------------------------------------------------
    public function __construct($table){
        $this->table = $table;
        $this->pdo = new PDO($this->dsn, 'root', '');
    }
     // -------------------all--------------------------------------------
    function all($where = '', $other = ''){
        $sql = "select * from `$this->table` ";
        $sql=$this->sql_all($sql,$where,$other);
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
     // -----------------count---------------------------------
    function count( $where = '', $other = ''){
        $sql = "select count(*) from `$this->table` ";
        $sql=$this->sql_all($sql,$where,$other);
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }
       // ---------------------find----------------------------------
      private function math($math,$col,$array='',$other=''){
        $sql = "select $math(`$col`) from `$this->table` ";
        $sql=$this->sql_all($sql,$array,$other);
        return $this->pdo->query($sql)->fetchColumn();
      }
      function sum($col='',$where='',$other=''){
        return $this->math('sum',$col,$where,$other);
      }
      function max($col='',$where='',$other=''){
        return $this->math('max',$col,$where,$other);
      }
      function min($col='',$where='',$other=''){
        return $this->math('sum',$col,$where,$other);
      }
      function avg($col='',$where='',$other=''){
        return $this->math('avg',$col,$where,$other);
      }
    function find($id){
        $sql = "select * from `$this->table` ";
        // 根據提供的 $id 參數的類型，動態添加 WHERE 條件到 SQL 語句中
        if (is_array($id)) {
            // 如果 $id 是陣列，將陣列元素轉換成 SQL 的條件語句
            $tmp = $this->a2s($id);
            $sql .= " where " . join(" && ", $tmp);
        } else if (is_numeric($id)) {
            // 如果 $id 是數字，直接條件是 ID 等於提供的數字
            $sql .= " where `id`='$id'";
        } else {
            echo "錯誤:參數的資料型態比須是數字或陣列";
        }//echo 'find=>'.$sql;
         // 執行 SQL 查詢並取得結果的第一行，以關聯陣列的形式返回
        $row = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        // 返回查詢結果
        return $row;
    }
    // -------------------------save---------------------------------------
    function save($array){
         // 檢查資料陣列中是否包含 'id' 欄位
        if(isset($array['id'])){
            // 如果包含 'id'，則執行 UPDATE 語句
            $sql = "update `$this->table` set ";
            // 檢查資料陣列是否非空
            if (!empty($array)) {
                // 將資料陣列轉換成 SET 子句的形式
                $tmp = $this->a2s($array);
            } else {
                echo "錯誤:缺少要編輯的欄位陣列";
            }
            // 將 SET 子句添加到 UPDATE 語句中
            $sql .= join(",", $tmp);
            // 添加 WHERE 條件，確保只更新指定 'id' 的資料
            $sql .= " where `id`='{$array['id']}'";
        }else{
            // 如果資料陣列中不包含 'id'，則執行 INSERT 語句
            $sql = "insert into `$this->table` ";
             // 取得資料陣列的所有欄位名稱，作為 INSERT 的欄位部分
            $cols = "(`" . join("`,`", array_keys($array)) . "`)";
             // 取得資料陣列的所有值，作為 INSERT 的值部分
            $vals = "('" . join("','", $array) . "')";
             // 將 INSERT 的欄位和值部分添加到 SQL 語句中
            $sql .= $cols . " values " . $vals;
        }
        // 使用 PDO 類別的 exec 方法執行最終生成的 SQL 語句
        return $this->pdo->exec($sql);
    }
    // ----------------------del-------------------------------------
    function del($id){
          // 建立基本的 SQL DELETE 語句，用於刪除資料表中的資料
        $sql = "delete from `$this->table` where ";
        // 根據提供的 $id 參數的類型，動態添加 WHERE 條件到 SQL 語句中
        if (is_array($id)) {
              // 如果 $id 是陣列，將陣列元素轉換成 SQL 的條件語句
            $tmp = $this->a2s($id);
            $sql .= join(" && ", $tmp);
        } else if (is_numeric($id)) {
            // 如果 $id 是數字，條件是 ID 等於提供的數字
            $sql .= " `id`='$id'";
        } else {
            echo "錯誤:參數的資料型態比須是數字或陣列";
        } //echo $sql;
         // 使用 PDO 類別的 exec 方法執行最終生成的 SQL 語句，實現資料刪除
        return $this->pdo->exec($sql);
    }
    // -------------------------------------------------------------
    function q($sql){
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    // -----------------------------------------------------------
    private function a2s($array){
        // 用於存儲欄位和值的陣列
        foreach ($array as $col => $value) {
             // 將每個欄位和值轉換成形如 "`欄位名`='值'" 的格式，並存入 $tmp 陣列中
            $tmp[] = "`$col`='$value'";
        }
        // 返回包含所有轉換後欄位和值的陣列
        return $tmp;
    }
     // 用於構建通用的 SQL 語句，包括 WHERE 和其他條件
    private function sql_all($sql,$array,$other){
        // 檢查是否有指定資料表名稱
        if (isset($this->table) && !empty($this->table)) {
            // 如果 $array 是陣列，表示有 WHERE 條件
            if (is_array($array)) {
                // 如果 $array 不為空，轉換為 SQL 中的條件語句ㄇ
                if (!empty($array)) {
                    $tmp = $this->a2s($array);
                    $sql .= " where " . join(" && ", $tmp);
                }
            } else {
                  // 如果 $array 不是陣列，直接加到 SQL 中
                $sql .= " $array";
            }
    
            $sql .= $other;
            // echo $sql;
            // 返回構建好的 SQL 語句
            return $sql;
        } else {
            echo "錯誤:沒有指定的資料表名稱";
        }
    }
}

// ----------------dd------------------------------
function dd($array){
    echo "<pre>";
    print_r($array);
    echo "</pre>";
}
// ------------------------------------------------
$Title=new DB('titles');
?>