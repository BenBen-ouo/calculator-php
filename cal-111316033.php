<?php
/*
謝百泓 111316033

操作說明：
1. 使用滑鼠按下小算盤上的數字、小數點與四則運算符號。
2. 按下「=」後顯示計算結果。
3. 按下「C」可清除所有輸入與運算狀態。
4. 按下「CE」可清除目前顯示的數字。
5. 按下「←」可刪除目前顯示數字的最後一位。
6. 按下「±」可切換目前數字的正負號。
7. 除以 0 時會顯示 Error。

符合的評分標準及自評應得的分數：
1. 支援整數所有計算：符合，+80%
2. 支援小數：符合，+20%
3. 程式檔案遵照規定命名為 cal-111316033.php：符合
4. 主程式開始包含作者、操作說明、評分標準等資訊：符合
5. 程式有適當註解：符合

自評分數：100

請說明不同的地方或增加的功能：
本程式是由先前自己撰寫的 JSP 小算盤改寫成 PHP 版本。
主要修改：
1. 將 JSP 語法改成 PHP 語法。
2. 將 BigDecimal 運算改成 PHP 的數值運算與格式化處理。
3. 保留 CE、←、± 等額外功能。
4. 保留 hidden input 保存計算狀態，使每次按鈕提交後仍能延續運算。

其他有利於評分的說明：
1. 本程式僅使用一個 PHP 檔案完成。
2. 使用 POST 表單送出按鈕資料，避免使用 JavaScript。
3. 使用 hidden input 保存 display、stored、operator、resetNext 等狀態。
4. 輸出時使用 htmlspecialchars()，避免特殊字元造成 HTML 顯示問題。

使用 AI 輔助時提供的 prompt：
「請將我先前寫過的 JSP 小算盤改寫成單一 PHP 檔案，
不可使用 JavaScript、Applet 或外掛程式，
要支援整數、小數、加減乘除。」

修改過程：
1. 將原本 JSP 的 page import、BigDecimal、request.getParameter 改成 PHP 寫法。
2. 將 JSP function 改成 PHP function。
3. 將 out.print 改成 PHP echo。
4. 將 HTML 表單保留，但把 <%= %> 改成 PHP 的 echo。
5. 加入 htmlspecialchars() 處理輸出安全。
*/

// 將字串轉成數字，若格式錯誤則回傳 0
function toNumber($value)
{
    if ($value === null || trim($value) === "" || $value === "Error") {
        return 0;
    }

    if (!is_numeric($value)) {
        return 0;
    }

    return (float)$value;
}

// 格式化計算結果，移除不必要的小數點與 0
function formatNumber($number)
{
    if (!is_finite($number)) {
        return "Error";
    }

    // 先限制小數位數，避免出現過長浮點數
    $text = rtrim(rtrim(sprintf("%.12f", $number), "0"), ".");

    if ($text === "-0") {
        return "0";
    }

    return $text;
}

// 執行四則運算
function calculate($left, $right, $operator)
{
    $a = toNumber($left);
    $b = toNumber($right);

    if ($operator === null || $operator === "") {
        return $right;
    }

    if ($operator === "+") {
        $result = $a + $b;
    } elseif ($operator === "-") {
        $result = $a - $b;
    } elseif ($operator === "*") {
        $result = $a * $b;
    } elseif ($operator === "/") {
        if ($b == 0) {
            return "Error";
        }
        $result = $a / $b;
    } else {
        return $right;
    }

    return formatNumber($result);
}

// 取得畫面目前顯示值
$display = $_POST["display"] ?? "0";
if ($display === "") {
    $display = "0";
}

// stored 用來記錄前一個數字
$stored = $_POST["stored"] ?? "";

// operator 用來記錄目前等待執行的運算子
$operator = $_POST["operator"] ?? "";

// resetNext 表示下一次輸入數字時是否要清空目前顯示值
$resetNext = $_POST["resetNext"] ?? "false";

// 取得使用者按下的按鈕
$btn = $_POST["btn"] ?? null;

if ($btn !== null) {

    // C：清除全部狀態
    if ($btn === "C") {
        $display = "0";
        $stored = "";
        $operator = "";
        $resetNext = "false";
    }

    // CE：只清除目前顯示數字，不清除已儲存的運算子
    elseif ($btn === "CE") {
        $display = "0";
        $resetNext = "false";
    }

    // ←：刪除最後一個字元
    elseif ($btn === "←") {
        if ($display === "Error" || strlen($display) <= 1) {
            $display = "0";
        } else {
            $display = substr($display, 0, strlen($display) - 1);

            if ($display === "-") {
                $display = "0";
            }
        }
    }

    // ±：切換正負號
    elseif ($btn === "±") {
        if ($display !== "0" && $display !== "Error") {
            if (substr($display, 0, 1) === "-") {
                $display = substr($display, 1);
            } else {
                $display = "-" . $display;
            }
        }
    }

    // 小數點輸入
    elseif ($btn === ".") {
        if ($resetNext === "true" || $display === "Error") {
            $display = "0.";
            $resetNext = "false";
        } elseif (strpos($display, ".") === false) {
            $display = $display . ".";
        }
    }

    // 數字輸入
    elseif (preg_match("/^[0-9]$/", $btn)) {
        if ($resetNext === "true" || $display === "Error") {
            $display = $btn;
            $resetNext = "false";
        } else {
            if ($display === "0") {
                $display = $btn;
            } else {
                $display = $display . $btn;
            }
        }
    }

    // 四則運算子
    elseif ($btn === "+" || $btn === "-" || $btn === "*" || $btn === "/") {

        // 若已經有前一個數字與運算子，則先計算前一次結果
        if ($stored !== "" && $operator !== "" && $resetNext === "false") {
            $display = calculate($stored, $display, $operator);
        }

        $stored = $display;
        $operator = $btn;
        $resetNext = "true";
    }

    // 等號：執行目前儲存的運算
    elseif ($btn === "=") {
        if ($stored !== "" && $operator !== "") {
            $display = calculate($stored, $display, $operator);
            $stored = "";
            $operator = "";
            $resetNext = "true";
        }
    }
}

// 避免輸出到 HTML 時產生特殊字元問題
$safeDisplay = htmlspecialchars($display, ENT_QUOTES, "UTF-8");
$safeStored = htmlspecialchars($stored, ENT_QUOTES, "UTF-8");
$safeOperator = htmlspecialchars($operator, ENT_QUOTES, "UTF-8");
$safeResetNext = htmlspecialchars($resetNext, ENT_QUOTES, "UTF-8");
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>PHP 小算盤</title>

    <!-- 調整小算盤外觀，使其接近 Windows 11 風格 -->
    <style>
        body {
            font-family: "Segoe UI", "Microsoft JhengHei", sans-serif;
            background: #f3f3f3;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .calculator {
            width: 360px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
            padding: 18px;
        }

        .title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #333333;
        }

        .status {
            text-align: right;
            font-size: 14px;
            color: #666666;
            height: 20px;
            margin-bottom: 4px;
        }

        .display {
            width: 100%;
            height: 70px;
            box-sizing: border-box;
            border: none;
            background: #f8f8f8;
            border-radius: 12px;
            text-align: right;
            font-size: 36px;
            font-weight: 500;
            padding: 10px;
            margin-bottom: 14px;
            color: #111111;
        }

        .buttons {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }

        button {
            height: 58px;
            border: none;
            border-radius: 10px;
            font-size: 20px;
            background: #eeeeee;
            cursor: pointer;
        }

        button:hover {
            background: #dddddd;
        }

        .operator {
            background: #dbeafe;
        }

        .operator:hover {
            background: #bfdbfe;
        }

        .equal {
            background: #2563eb;
            color: white;
        }

        .equal:hover {
            background: #1d4ed8;
        }

        .clear {
            background: #fee2e2;
        }

        .clear:hover {
            background: #fecaca;
        }
    </style>
</head>

<body>
    <div class="calculator">
        <div class="title">標準小算盤</div>

        <form method="post">
            <!-- hidden input 用來在每次送出表單時保存計算狀態 -->
            <input type="hidden" name="display" value="<?php echo $safeDisplay; ?>">
            <input type="hidden" name="stored" value="<?php echo $safeStored; ?>">
            <input type="hidden" name="operator" value="<?php echo $safeOperator; ?>">
            <input type="hidden" name="resetNext" value="<?php echo $safeResetNext; ?>">

            <div class="status">
                <?php
                // 顯示目前等待運算的前一個數字與運算子
                if ($stored !== "" && $operator !== "") {
                    echo $safeStored . " " . $safeOperator;
                }
                ?>
            </div>

            <!-- 顯示目前輸入或計算結果 -->
            <input class="display" type="text" value="<?php echo $safeDisplay; ?>" readonly>

            <div class="buttons">
                <button class="clear" type="submit" name="btn" value="CE">CE</button>
                <button class="clear" type="submit" name="btn" value="C">C</button>
                <button type="submit" name="btn" value="←">←</button>
                <button class="operator" type="submit" name="btn" value="/">÷</button>

                <button type="submit" name="btn" value="7">7</button>
                <button type="submit" name="btn" value="8">8</button>
                <button type="submit" name="btn" value="9">9</button>
                <button class="operator" type="submit" name="btn" value="*">×</button>

                <button type="submit" name="btn" value="4">4</button>
                <button type="submit" name="btn" value="5">5</button>
                <button type="submit" name="btn" value="6">6</button>
                <button class="operator" type="submit" name="btn" value="-">−</button>

                <button type="submit" name="btn" value="1">1</button>
                <button type="submit" name="btn" value="2">2</button>
                <button type="submit" name="btn" value="3">3</button>
                <button class="operator" type="submit" name="btn" value="+">+</button>

                <button type="submit" name="btn" value="±">±</button>
                <button type="submit" name="btn" value="0">0</button>
                <button type="submit" name="btn" value=".">.</button>
                <button class="equal" type="submit" name="btn" value="=">=</button>
            </div>
        </form>
    </div>
</body>
</html>