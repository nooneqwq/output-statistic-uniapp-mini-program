<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|


Route::get('/great', function (Request $request) {
    sleep(1);
    return "hello world from lavarel";
});
*/

require '../vendor/autoload.php';
//引入Excel读写库的命名空间
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


Route::post('/login', function (Request $request) {
    //获取code
    $code = $request->get('code');
    //调用code2session接口，返回openID和sessionkey
    $url = 'https://api.weixin.qq.com/sns/jscode2session';
    $param = [
        'appid' => env('WX_APPID', ''),
        'secret' => env('WX_APPSECRET', ''),
        'js_code' => $code,
        'grant_type' => 'authorization_code'
    ];
    //拼接字符串
    $url .= '?' . http_build_query($param);
    $res = file_get_contents($url);
    $res = json_decode($res, true);
    $openId = $res['openid'];
    $sessionKey = $res['session_key'];
    $passWord = $res['openid'] . $res['session_key'];
    //自定义密码
    $realPassWord = '';
    //生成自定义密码
    for ($i = 0; $i < strlen($passWord); $i += 2) {
        $realPassWord .= $passWord[$i];
    }
    //存入数据库，若已存在数据库文件，则直接写入数据
    if (file_exists('../public/newDb.db')) {


        class MyDB extends SQLite3
        {
            function __construct()
            {
                $this->open('newDb.db');
            }
        }
        $db = new MyDB();
        if (!$db) {
            echo $db->lastErrorMsg();
        } else {
            //echo "Opened database successfully\n";
        }
        $sql = <<<EOF
      INSERT INTO LIST (REALPASSWORD, OPENID, SESSIONKEY)
      VALUES ('$realPassWord',  '$openId', '$sessionKey' );
EOF;



        $ret = $db->exec($sql);
        if (!$ret) {
            //echo $db->lastErrorMsg();
        } else {
            //echo "Table created successfully\n";
        }
        $db->close();
    }
    //不存在数据库文件，则新建数据库
    else {
        class MyDB extends SQLite3
        {
            function __construct()
            {
                $this->open('newDb.db');
            }
        }
        $db = new MyDB();
        if (!$db) {
            echo $db->lastErrorMsg();
        } else {
            //echo "Opened database successfully\n";
        }
        $sql = <<<EOF
        CREATE TABLE "LIST" (
        "id"    INTEGER NOT NULL,
        "REALPASSWORD"  TEXT    NOT NULL,
        "OPENID"    TEXT   NOT NULL,
        "SESSIONKEY"    TEXT    NOT NULL,
        PRIMARY KEY("id" AUTOINCREMENT)
        );
        EOF;

        $ret = $db->exec($sql);
        if (!$ret) {
            //echo $db->lastErrorMsg();
        } else {
            //echo "Table created successfully\n";
        }
        $sql1 = <<<EOF
      INSERT INTO LIST (REALPASSWORD, OPENID, SESSIONKEY)
      VALUES ('$realPassWord',  '$openId', '$sessionKey' );
EOF;



        $db->exec($sql1);
        $db->close();
    }

    //返回自定义密码
    if (!empty($res['openid'])) {
        return [
            'code' => 0,
            'data' => [
                'openid' => $openId,
                'passWord' => $realPassWord
            ],
            'msg' => 'success'
        ];
    } else {
        return [
            'code' => -3,
            'data' => [],
            'msg' => 'wrong code'
        ];
    }
});
Route::post('/verify', function (Request $request) {
    $passWord = $request->get('code');
    $passWord = (string)$passWord;
    //查询数据库是否存在realpassword，若存在，返回code1 不存在 返回code0 
    class MyDB extends SQLite3
    {
        function __construct()
        {
            $this->open('newDb.db');
        }
    }
    $db = new MyDB();
    $stmt = $db->prepare("SELECT * FROM LIST WHERE REALPASSWORD =:bar");
    $stmt->bindParam(':bar', $passWord, SQLITE3_TEXT);


    $ret = $stmt->execute();
    while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
        echo "id = " . $row['id'] . "\n";
    }
    if (!$ret) {
        return [
            'code' => 0,
            'data' => [
                'passWord' => $passWord
            ],
            'msg' => 'verified'
        ];
    } else {

        return [
            'code' => 1,
            'data' => [
                'passWord' => $passWord
            ],
            'msg' => 'verified'
        ];
    }
    $db->close();
});
Route::post('/update', 'UserController@update');
Route::post('/updatedata', function (Request $request) {
    $totalOutput = $request->get('totalOutput');
    $cmmOutput  = $request->get('cmmOutput');
    $cmmReject = $request->get('cmmReject');
    $cmmDefectNum = $request->get('cmmDefectNum');
    $cmmStopNum = $request->get('cmmStopNum');
    $cigRecPanl = $request->get('cigRecPanl');
    $sPackingOutput = $request->get('sPackingOutput');
    $sPackingReject = $request->get('sPackingReject');
    $sPackingDefectNum = $request->get('sPackingDefectNum');
    $sealingMRejectNum = $request->get('sealingMRejectNum');
    $sPackingStopNum = $request->get('sPackingStopNum');
    $mNumber = $request->get('mNumber');
    date_default_timezone_set("PRC");
    //获取当前时间，写入excel
    $date =  date("Y-m-d H:i:s");
    if (file_exists('../public/outputData.db')) {
        class MyDB extends SQLite3
        {
            function __construct()
            {
                $this->open('outputData.db');
            }
        }
        $db = new MyDB();
        if (!$db) {
            echo $db->lastErrorMsg();
        } else {
            //echo "Opened database successfully\n";
        }
        $sql = <<<EOF
      INSERT INTO DATA (TOTALOUTPUT, MNUMBER, DATE, CMMREJECT, CMMDEFECTNUM, CMMSTOPNUM, SPACKINGREJECT, SPACKINGDEFECTNUM, SPACKINGSTOPNUM)
      VALUES ('$totalOutput', '$mNumber','$date', '$cmmReject', '$cmmDefectNum', '$cmmStopNum', '$sPackingReject', '$sPackingDefectNum', '$sPackingStopNum');
EOF;
        $db->exec($sql);
        $db->close();
    }
    //不存在数据库文件，则新建数据库并存储
    else {
        class MyDB extends SQLite3
        {
            function __construct()
            {
                $this->open('outputData.db');
            }
        }
        $db = new MyDB();
        if (!$db) {
            echo $db->lastErrorMsg();
        } else {
            //echo "Opened database successfully\n";
        }
        $sql = <<<EOF
        CREATE TABLE "DATA" (
        "ID" INTEGER NOT NULL,
        "TOTALOUTPUT" INTEGER NOT NULL,
        "MNUMBER" INTEGER NOT NULL,
        "DATE" TEXT NOT NULL,
        "CMMREJECT"	INTEGER NOT NULL,
        "CMMDEFECTNUM"	INTEGER NOT NULL,
        "CMMSTOPNUM"	INTEGER NOT NULL,
        "SPACKINGREJECT"	INTEGER NOT NULL,
        "SPACKINGDEFECTNUM"	INTEGER NOT NULL,
        "SPACKINGSTOPNUM"	INTEGER NOT NULL,
        PRIMARY KEY("ID" AUTOINCREMENT)
        );
        EOF;

        $ret = $db->exec($sql);
        if (!$ret) {
            echo $db->lastErrorMsg();
        } else {
            //echo "Table created successfully\n";
        }
        $sql1 = <<<EOF
      INSERT INTO DATA (TOTALOUTPUT, MNUMBER, DATE, CMMREJECT, CMMDEFECTNUM, CMMSTOPNUM, SPACKINGREJECT, SPACKINGDEFECTNUM, SPACKINGSTOPNUM)
      VALUES ('$totalOutput', '$mNumber','$date', '$cmmReject', '$cmmDefectNum', '$cmmStopNum', '$sPackingReject', '$sPackingDefectNum', '$sPackingStopNum');
EOF;

        $db->exec($sql1);

        $db->close();
    }

    //根据上传的机器号生成要写入的excel单元格位置
    function calcLocation($para, $mNumber)
    {
        $cell2 = 'C';
        $cell3 = 'D';
        $cell4 = 'E';
        $cell5 = 'F';
        $cell6 = 'G';
        $cell7 = 'H';
        $cell8 = 'I';
        $cell9 = 'J';
        $cell10 = 'K';
        $cell11 = 'L';
        $cell12 = 'M';
        $cell0 = 'B';
        $cell1 = (string)($mNumber + $para);
        global $cellm;
        global $cell;
        global $cell121;
        global $cell13;
        global $cell14;
        global $cell15;
        global $cell16;
        global $cell17;
        global $cell18;
        global $cell19;
        global $cell20;
        global $cell21;
        global $cell22;
        $cellm = 'A' . $cell1;
        $cell = $cell0 . $cell1;
        $cell121 = $cell2 . $cell1;
        $cell13 = $cell3 . $cell1;
        $cell14 = $cell4 . $cell1;
        $cell15 = $cell5 . $cell1;
        $cell16 = $cell6 . $cell1;
        $cell17 = $cell7 . $cell1;
        $cell18 = $cell8 . $cell1;
        $cell19 = $cell9 . $cell1;
        $cell20 = $cell10 . $cell1;
        $cell21 = $cell11 . $cell1;
        $cell22 = $cell12 . $cell1;
    }
    //机器号不连续，不同区间加减不同数值匹配单元格位置
    if ($mNumber < 9) {
        $para = 1;
        calcLocation($para, $mNumber);
    } elseif ($mNumber < 16) {
        $para = -1;
        calcLocation($para, $mNumber);
    } elseif ($mNumber < 58) {
        $para = -41;
        calcLocation($para, $mNumber);
    } elseif ($mNumber < 66) {
        $para = -45;
        calcLocation($para, $mNumber);
    }
    //若已存在Excel表格，直接写入
    if (file_exists("../public/data.xlsx")) {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
        $spreadsheet = $reader->load("../public/data.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        global $cellm;
        global $cell;
        global $cell121;
        global $cell13;
        global $cell14;
        global $cell15;
        global $cell16;
        global $cell17;
        global $cell18;
        global $cell19;
        global $cell20;
        global $cell21;
        global $cell22;
        $sheet
            ->setCellValue($cellm, $mNumber)
            ->setCellValue($cell, $totalOutput)
            ->setCellValue($cell121, $cmmOutput)
            ->setCellValue($cell13, $cmmReject)
            ->setCellValue($cell14, $cmmDefectNum)
            ->setCellValue($cell15, $cmmStopNum)
            ->setCellValue($cell16, $cigRecPanl)
            ->setCellValue($cell17, $sPackingOutput)
            ->setCellValue($cell18, $sPackingReject)
            ->setCellValue($cell19, $sPackingDefectNum)
            ->setCellValue($cell20, $sealingMRejectNum)
            ->setCellValue($cell21, $sPackingStopNum)
            ->setCellValue($cell22, $date);
        $writer = new Xlsx($spreadsheet);
        $writer->save('data.xlsx');
    }
    //否则先创建再写入
    else {
        global $cellm;
        global $cell;
        global $cell121;
        global $cell13;
        global $cell14;
        global $cell15;
        global $cell16;
        global $cell17;
        global $cell18;
        global $cell19;
        global $cell20;
        global $cell21;
        global $cell22;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet
            ->setCellValue('A1', '机台号/项目')
            ->setCellValue('B1', '提升器产量')
            ->setCellValue('C1', '卷烟机产量')
            ->setCellValue('D1', '卷烟机剔烟数')
            ->setCellValue('E1', '卷烟机空头剔烟数')
            ->setCellValue('F1', '卷烟机停机次数')
            ->setCellValue('G1', '烟支回收盘数')
            ->setCellValue('H1', '小包机产量')
            ->setCellValue('I1', '小包机剔除数')
            ->setCellValue('J1', '小包机空头剔除数')
            ->setCellValue('K1', '油封机剔烟数')
            ->setCellValue('L1', '小包机停机次数')
            ->setCellValue('M1', '提交时间')
            ->setCellValue($cellm, $mNumber)
            ->setCellValue($cell, $totalOutput)
            ->setCellValue($cell121, $cmmOutput)
            ->setCellValue($cell13, $cmmReject)
            ->setCellValue($cell14, $cmmDefectNum)
            ->setCellValue($cell15, $cmmStopNum)
            ->setCellValue($cell16, $cigRecPanl)
            ->setCellValue($cell17, $sPackingOutput)
            ->setCellValue($cell18, $sPackingReject)
            ->setCellValue($cell19, $sPackingDefectNum)
            ->setCellValue($cell20, $sealingMRejectNum)
            ->setCellValue($cell21, $sPackingStopNum)
            ->setCellValue($cell22, $date);
        $writer = new Xlsx($spreadsheet);
        $writer->save('data.xlsx');
    }


    return [
        'code' => 0,
        'data' => [
            'totalOutput' => $totalOutput,
            'mNumber' => $mNumber
        ],
        'msg' => '数据上传成功'
    ];
});
Route::post('/calcAvg', function (Request $request) {
    $totalOutput = $request->get('totalOutput');
    class MyDB1 extends SQLite3
    {
        function __construct()
        {
            $this->open('outputData.db');
        }
    }
    $db = new MyDB1();
    if (!$db) {
        echo $db->lastErrorMsg();
    } else {
        //echo "Opened database successfully\n";
    }
    $sql = <<<EOF
      SELECT * from DATA;
EOF;

    $ret = $db->query($sql);
    while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
        echo "ID = " . $row['ID'] . "\n";
        echo "NAME = " . $row['NAME'] . "\n";
        echo "ADDRESS = " . $row['ADDRESS'] . "\n";
        echo "SALARY =  " . $row['SALARY'] . "\n\n";
    }
    
    $db->close();
});
