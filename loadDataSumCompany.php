<?php

/**
 * Created by PhpStorm.
 * User: ITISME
 * Date: 19/1/2560
 * Time: 11:42
 */
session_start();
if (!isset($_SESSION['UserName'])) {
    header("Location:login.html");
}
//date_default_timezone_set("Asia/Bangkok");
$start = microtime(true);
//$branch = $_GET['branch'];
//$project = $_GET['project'];
$searchYear = $_GET['Year'];
$comType = $_GET['ComType'];
//echo $dateStart;
//echo "<br>";
//echo $dateEnd;


?>
    <script type="text/javascript">

        $(document).ready(function()
        {

        });


        function SummaryHeader()
        {
            var rowCount = $('#TableData tr').length;

            $("#H_ContactTotal").html($("#F_Row").text());
            $("#H_IncomTotal").text($("#F_Income").text());
            $("#H_StockCostTotal").text($("#F_StockCost").text());
            $("#H_PayrollTotal").text($("#F_Salary").text());
            $("#H_NetTotal").text($("#F_NetTotal").text());
        }


        function show_hide_row(row)
        {
            $("#"+row).toggle();
        }
    </script>


<?php
include 'include/dbConfigPDO.php';
include 'include/function.php';
//echo $_SESSION['ToolStatus'] ;
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
//echo $_SESSION['ToolStatus'];

if ($comType == "comp") {// บจก
    $sql = "select a.*,b.salary,c.Income,d.NetPrice from (
            --เงินเดือน
            select month( convert(varchar(13),b.paydate,111))  as docdate
            from CC_Main_CO..Wage b
             where YEAR(convert(varchar(13),b.paydate,111)) = '".$searchYear."'
             union
            --รายรับ
             select month(convert(varchar(13),docdate,111)) as docdate
              from CC_Main_CO..artaxinvoice 
              where YEAR(convert(varchar(13),docdate,111)) = '".$searchYear."'
              union
            -- รายจ่ายสำนักงาน
            select  month(convert(varchar(15),A.DocDate,111)) as DocDate
            from APPay A 
             where   YEAR(convert(varchar(15),A.DocDate,111)) = '".$searchYear."'
            ) a left outer join (
            --เงินเดือน
            select month( convert(varchar(13),b.paydate,111))  as docdate,sum(SalaryGrandTotal) as Salary
                    from CC_Main_CO..WageDetail a 
					left outer join CC_Main_CO..Wage b on a.MonthCycle=b.MonthCycle and a.GRoupworkID=b.GRoupworkID and a.ProjectID = b.ProjectID
                    where 1=1 and a.isholdpay = 2 and b.isProcessed = 'Y' and YEAR(convert(varchar(13),b.paydate,111)) = '".$searchYear."'
              group by  month( convert(varchar(13),b.paydate,111)) 
              ) b on a.docdate=b.docdate left outer join (
            --รายรับ
             select month(convert(varchar(13),docdate,111)) as docdate,sum(Netprice) as Income from CC_Main_CO..artaxinvoice 
              where 1=1 and YEAR(convert(varchar(13),docdate,111)) = '".$searchYear."'
              group by  month( convert(varchar(13),docdate,111)) ) c on a.docdate=c.docdate left outer join (
            -- รายจ่ายสำนักงาน
            select  month(convert(varchar(15),A.DocDate,111)) as DocDate,
                   sum(A.NetPrice) as NetPrice 
            from APPay A 
            Left outer join Vendor B on A.VendorID=B.VendorID 
             where  1=1 and YEAR(convert(varchar(15),A.DocDate,111)) = '".$searchYear."'
             group by  month( convert(varchar(15),A.DocDate,111)) ) d on a.docdate=d.docdate";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
}else{ //หจก.
    $sql = "select a.*,b.salary,c.Income,d.NetPrice from (
            --เงินเดือน
            select month( convert(varchar(13),b.paydate,111))  as docdate
            from CC_Main..Wage b
             where YEAR(convert(varchar(13),b.paydate,111)) = '".$searchYear."'
             union
            --รายรับ
             select month(convert(varchar(13),docdate,111)) as docdate
              from CC_Main..artaxinvoice 
              where YEAR(convert(varchar(13),docdate,111)) = '".$searchYear."'
              union
            -- รายจ่ายสำนักงาน
            select  month(convert(varchar(15),A.DocDate,111)) as DocDate
            from APPay A 
             where   YEAR(convert(varchar(15),A.DocDate,111)) = '".$searchYear."'
            ) a left outer join (
            --เงินเดือน
            select month( convert(varchar(13),b.paydate,111))  as docdate,sum(SalaryGrandTotal) as Salary
                    from CC_Main..WageDetail a 
					left outer join CC_Main..Wage b on a.MonthCycle=b.MonthCycle and a.GRoupworkID=b.GRoupworkID and a.ProjectID = b.ProjectID
                    where 1=1 and a.isholdpay = 2 and b.isProcessed = 'Y' and YEAR(convert(varchar(13),b.paydate,111)) = '".$searchYear."'
              group by  month( convert(varchar(13),b.paydate,111)) 
              ) b on a.docdate=b.docdate left outer join (
            --รายรับ
             select month(convert(varchar(13),docdate,111)) as docdate,sum(Netprice) as Income from CC_Main..artaxinvoice 
              where 1=1 and YEAR(convert(varchar(13),docdate,111)) = '".$searchYear."'
              group by  month( convert(varchar(13),docdate,111)) ) c on a.docdate=c.docdate left outer join (
            -- รายจ่ายสำนักงาน
          SELECT  month(convert(varchar(15),A.DocDate,111)) as DocDate ,
                sum(CASE   
                      WHEN a.VendorID = 'G01-V0475' THEN b.amount/2
                      else b.amount
                   END)  as netprice 
                  FROM APPay a
                  left outer join appaytype b on a.docno=b.docno
                  where paytypeid <> 'CHQ1' and paytypeid <> 'CHQ2' and YEAR(convert(varchar(15),A.DocDate,111)) = '".$searchYear."'
                   group by  month( convert(varchar(15),A.DocDate,111))  ) d on a.docdate=d.docdate order by a.docdate asc";

    $stmt = $connPart->prepare($sql);
    $stmt->execute();

}



// <th align='center'>วันที่เริ่ม</th>
//<th align='center'>วันที่สิ้นสุด</th>
echo "<div style='overflow-x:auto;'>
                <table id='TableData' border='1' cellspacing='1' cellpadding='1' class='gridtable4'>
                    <tr>
                          <th  style='font-family: San Francisco;font-size: 12px;text-align: center'>เดือน<br></th>
                          <th  style='font-family: San Francisco;font-size: 12px;text-align: center'>รายรับ</th>
                          <th  style='font-family: San Francisco;font-size: 12px;text-align: center'>เงินเดือน</th>
                          <th  style='font-family: San Francisco;font-size: 12px;text-align: center'>ค่าใช้จ่ายสำนักงาน</th>
                          <th  style='font-family: San Francisco;font-size: 12px;text-align: center'>รายได้สุทธิ</th>
                    </tr>";

$r=1;
$TotalIncome=0;
$TotalNetPrice=0;
$TotalSalary=0;
   while($row = $stmt->fetch( PDO::FETCH_ASSOC )) {


       $rowNetTotal = $row['Income']-$row['salary']-$row['NetPrice'];

       if ($rowNetTotal < 0) {
           $styNetIncome = "Netincomeleadingzero";
          // $styNetPrice = "border: 1px solid #ddd;";
       }else{
           $styNetIncome = "Netincome";
         //  $styNetPrice = "border: 1px solid green;";
       }

       if ($TotalIncome <= 0){
           $styIncome = "Incomeleadingzero";
       }else if($TotalIncome > 0){
           $styIncome = "Income";
       }

        echo"<tr onclick=\"show_hide_row('hidden_row".$r."');\" >
                          <td  style='padding: 1px;font-family: San Francisco;font-size: 12px;text-align: center'>".$row['docdate']."</td>
                          <td style='padding: 1px;font-family: San Francisco;font-size: 12px;text-align: right'>".number_format($row['Income'], 2, '.', ',')."</td>
                          <td style='padding: 1px;font-family: San Francisco;font-size: 12px;text-align: right'>".number_format(round($row['salary'] ),2, '.', ',')."</td>
                          <td style='padding: 1px;font-family: San Francisco;font-size: 12px;text-align: right'>".number_format($row['NetPrice'], 2, '.', ',')."</td>
                          <td style='padding: 1px;font-family: San Francisco;font-size: 12px;text-align: right' class='".$styNetIncome."'>".number_format($row['Income']-$row['salary']-$row['NetPrice'], 2, '.', ',')."</td>
                    </tr>
            <tr id='hidden_row".$r."' class='hidden_row' hidden>
                        <td colspan='6' align='center'></td>
            </tr>";

       $TotalIncome = $TotalIncome + $row['Income'];
       $TotalNetPrice = $TotalNetPrice + $row['NetPrice'];
       $TotalSalary = $TotalSalary + $row['salary'];

    }

    $NetIncome = $TotalIncome-$TotalSalary-$TotalNetPrice;

   if ($NetIncome < 0) {

       $styNetIncome = "Netincomeleadingzero";
   }else{
       $styNetIncome = "Netincome";

   }

    if ($TotalIncome <= 0){
        $styIncome = "Incomeleadingzero";
    }else if($TotalIncome > 0){
        $styIncome = "Income";
    }

echo "<tr>
                <td style='font-family: San Francisco;font-size: 12px;text-align: center;font-weight: bold;'>รวม</td>
                <td style='font-family: San Francisco;font-size: 12px;text-align: right;font-weight: bold;' class='".$styIncome."'>".number_format($TotalIncome, 2, '.', ',')."</td>
                <td style='font-family: San Francisco;font-size: 12px;text-align: right;font-weight: bold;'>".number_format($TotalSalary, 2, '.', ',')."</td>
                <td style='font-family: San Francisco;font-size: 12px;text-align: right;font-weight: bold;'>".number_format($TotalNetPrice, 2, '.', ',')."</td>
                <td style='font-family: San Francisco;font-size: 12px;text-align: right;font-weight: bold;' class='".$styNetIncome."'>".number_format($NetIncome, 2, '.', ',')."</td>
</tr>
                  </table>
</div><br>";

//echo '<script type="text/javascript">',
//'SummaryHeader();',
//'</script>';

$end = microtime(true);
$creationtime = ($end - $start);
//printf("Page created in %.6f seconds.", $creationtime);
//sqlsrv_close( $conn1);

?>